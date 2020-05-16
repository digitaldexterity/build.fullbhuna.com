<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/documentfunctions.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10,2,7,6,5";
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

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varDocumentID_rsDocument = "-1";
if (isset($_GET['documentID'])) {
  $varDocumentID_rsDocument = $_GET['documentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocument = sprintf("SELECT documents.*, users.firstname, users.surname, uploads.filename AS originalname FROM documents LEFT JOIN users ON (users.ID = documents.userID) LEFT JOIN uploads ON (documents.uploadID = uploads.ID) WHERE documents.ID = %s ", GetSQLValueString($varDocumentID_rsDocument, "int"));
$rsDocument = mysql_query($query_rsDocument, $aquiescedb) or die(mysql_error());
$row_rsDocument = mysql_fetch_assoc($rsDocument);
$totalRows_rsDocument = mysql_num_rows($rsDocument);


$varCategoryID_rsCurrentFolder = "0";
if (isset($row_rsDocument['documentcategoryID'])) {
  $varCategoryID_rsCurrentFolder = $row_rsDocument['documentcategoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCurrentFolder = sprintf("SELECT * FROM documentcategory WHERE ID = %s", GetSQLValueString($varCategoryID_rsCurrentFolder, "int"));
$rsCurrentFolder = mysql_query($query_rsCurrentFolder, $aquiescedb) or die(mysql_error());
$row_rsCurrentFolder = mysql_fetch_assoc($rsCurrentFolder);
$totalRows_rsCurrentFolder = mysql_num_rows($rsCurrentFolder);

// security - check if user has permission to update within this folder or target select statements must go above

$pageaccess = true;
if(!($row_rsLoggedIn['usertypeID']>=9 || $row_rsLoggedIn['usertypeID']>=$row_rsCurrentFolder['writeaccess'] || ($row_rsCurrentFolder['writeaccess']==99 && $row_rsCurrentFolder['addedbyID'] == $row_rsLoggedIn['ID']))) {
	// not authorised to add 
	if(isset($_POST["MM_insert"])) {
		unset($_POST["MM_insert"]);
	}
	$error = "You are not authorised to add anything within the folder: ".$row_rsCurrentFolder['categoryname'];
	$pageaccess = false;
}

if(isset($_POST["MM_update"])) {
	$select = "SELECT * FROM documentcategory WHERE ID = ".GetSQLValueString($_POST['documentcategoryID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	
	if(!($row_rsLoggedIn['usertypeID']>=9 || $row_rsLoggedIn['usertypeID']>=$row['writeaccess'] || ($row['writeaccess']==99 && $row['addedbyID'] == $row_rsLoggedIn['ID']))) {
		// not authorised to update 
		
			unset($_POST["MM_update"]);
	
		$error = "You are not authorised to move anything to the folder: ".$row['categoryname'];
		
	}
}

if($pageaccess && isset($_GET['deletecategoryID']) && intval($_GET['deletecategoryID'])>0  && $_GET['token'] == md5(PRIVATE_KEY.$_GET['deletecategoryID'])) {
	$delete = "DELETE FROM documentincategory WHERE ID = ".intval($_GET['deletecategoryID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	header("location: modify_document.php?documentID=".intval($_GET['documentID'])."&defaultTab=1"); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocPrefs = "SELECT * FROM documentprefs";
$rsDocPrefs = mysql_query($query_rsDocPrefs, $aquiescedb) or die(mysql_error());
$row_rsDocPrefs = mysql_fetch_assoc($rsDocPrefs);
$totalRows_rsDocPrefs = mysql_num_rows($rsDocPrefs);

$colname_rsDocument = "-1";
if (isset($_GET['documentID'])) {
  $colname_rsDocument = $_GET['documentID'];
}

$_POST['filename'] = isset($_POST['file']) ? $_POST['file'] : @$_POST['filename'];

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	require_once('../../core/includes/upload.inc.php'); 
	$uploaded = getUploads(UPLOAD_ROOT,array(),"",0,0,"",array(),false);
	if(isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['error'])) {
		$error = $uploaded['filename'][0]['error'];
		unset($_POST['MM_update']);
	} else if (isset($uploaded['filename']) && is_array($uploaded['filename'][0]) && isset($uploaded['filename'][0]['newname'])) {
		// new file uploaded - save old one?
		if($row_rsDocPrefs['versioncontrol']==1 && intval($_POST['uploadID'])>0) { // version control
			$insert = "INSERT INTO documentversion (documentID, uploadID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['ID'], "int").",".GetSQLValueString($_POST['uploadID'], "int").",". GetSQLValueString($_POST['userID'], "int").",".GetSQLValueString($_POST['uploaddatetime'], "date").")";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
		}
		$_POST['filename'] = $uploaded['filename'][0]['newname'];
		$_POST['type'] = $uploaded['filename'][0]['type'];
		$_POST['uploadID'] = $uploaded['filename'][0]['uploadID'];
		$_POST['userID'] = $_POST['modifiedbyID'];
		$_POST['uploaddatetime'] = $_POST['modifieddatetime'];
		
	} else {
		$_POST['filename'] = $_POST['oldfilename'];
		$_POST['type'] = $_POST['oldtype'];
	}
}// end post

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE documents SET documentname=%s, documentcategoryID=%s, active=%s, filename=%s, uploaddatetime=%s, userID=%s, modifiedbyID=%s, modifieddatetime=%s, type=%s, `lock`=%s, uploadID=%s, `description`=%s WHERE ID=%s",
                       GetSQLValueString($_POST['documentname'], "text"),
                       GetSQLValueString($_POST['documentcategoryID'], "int"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['filename'], "text"),
                       GetSQLValueString($_POST['uploaddatetime'], "date"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['type'], "text"),
                       GetSQLValueString(isset($_POST['lock']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['uploadID'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	if(isset($_POST["documentincategoryID"]) && intval($_POST["documentincategoryID"])>0) {
		$makemain = isset($_POST['makemain']) ? true : false;
		$error = addDocumentToCategory($_POST['ID'],$_POST["documentincategoryID"],$_POST['modifiedbyID'], $makemain);
		$updateGoTo = "modify_document.php?documentID=".intval($_POST['ID'])."&defaultTab=1";
	} else {
  $updateGoTo = "/documents/index.php?categoryID=".intval($_POST['documentcategoryID']);
	}
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  if(!isset($error) || $error =="") {
  	header(sprintf("Location: %s", $updateGoTo));exit;
  }
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT ID FROM region";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);





$varUserType_rsFolders = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserType_rsFolders = $_SESSION['MM_UserGroup'];
}
$varCurrentCategory_rsFolders = "-1";
if (isset($row_rsDocument['documentcategoryID'])) {
  $varCurrentCategory_rsFolders = $row_rsDocument['documentcategoryID'];
}
$varUserID_rsFolders = "-1";
if (isset($row_rsLoggedIn['ID'])) {
  $varUserID_rsFolders = $row_rsLoggedIn['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFolders = sprintf("SELECT documentcategory.ID, documentcategory.categoryname, parentcategory.categoryname AS parentname, grandparentcategory.categoryname AS grandparentname, documentcategory.subcatofID, documentcategory.accessID, documentcategory.active, region.title AS site FROM documentcategory LEFT JOIN documentcategory AS parentcategory ON (documentcategory.subcatofID = parentcategory.ID) LEFT JOIN documentcategory AS grandparentcategory ON (parentcategory.subcatofID = grandparentcategory.ID) LEFT JOIN region ON (documentcategory.regionID =region.ID) WHERE documentcategory.ID = %s OR (documentcategory.active = 1 AND ((documentcategory.writeaccess = 99  AND documentcategory.addedbyID = %s) OR documentcategory.writeaccess <= %s)) ORDER BY documentcategory.regionID, documentcategory.categoryname", GetSQLValueString($varCurrentCategory_rsFolders, "int"),GetSQLValueString($varUserID_rsFolders, "int"),GetSQLValueString($varUserType_rsFolders, "int"));
$rsFolders = mysql_query($query_rsFolders, $aquiescedb) or die(mysql_error());
$row_rsFolders = mysql_fetch_assoc($rsFolders);
$totalRows_rsFolders = mysql_num_rows($rsFolders);



$colname_rsLastModified = "-1";
if (isset($_GET['documentID'])) {
  $colname_rsLastModified = $_GET['documentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastModified = sprintf("SELECT users.firstname, users.surname, documents.modifieddatetime, documents.type FROM documents LEFT JOIN users ON (users.ID = documents.modifiedbyID) WHERE documents.ID = %s", GetSQLValueString($colname_rsLastModified, "int"));
$rsLastModified = mysql_query($query_rsLastModified, $aquiescedb) or die(mysql_error());
$row_rsLastModified = mysql_fetch_assoc($rsLastModified);
$totalRows_rsLastModified = mysql_num_rows($rsLastModified);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsVersions = "-1";
if (isset($_GET['documentID'])) {
  $colname_rsVersions = $_GET['documentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVersions = sprintf("SELECT documentversion.*, uploads.filename, uploads.mimetype, uploads.filesize, uploads.newfilename, users.firstname, users.surname, uploads.filename FROM documentversion LEFT JOIN uploads ON (documentversion.uploadID = uploads.ID) LEFT JOIN users ON (documentversion.createdbyID = users.ID) WHERE documentID = %s ORDER BY documentversion.createddatetime ASC", GetSQLValueString($colname_rsVersions, "int"));
$rsVersions = mysql_query($query_rsVersions, $aquiescedb) or die(mysql_error());
$row_rsVersions = mysql_fetch_assoc($rsVersions);
$totalRows_rsVersions = mysql_num_rows($rsVersions);

$colname_rsCurrentOtherFolders = "-1";
if (isset($_GET['documentID'])) {
  $colname_rsCurrentOtherFolders = $_GET['documentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCurrentOtherFolders = sprintf("SELECT documentincategory.ID, documentcategory.categoryname, parentcategory.categoryname AS parentname FROM documentincategory LEFT JOIN documentcategory ON (documentincategory.categoryID = documentcategory.ID) LEFT JOIN documentcategory AS parentcategory ON (documentcategory.subcatofID = parentcategory.ID) WHERE documentID = %s", GetSQLValueString($colname_rsCurrentOtherFolders, "int"));
$rsCurrentOtherFolders = mysql_query($query_rsCurrentOtherFolders, $aquiescedb) or die(mysql_error());
$row_rsCurrentOtherFolders = mysql_fetch_assoc($rsCurrentOtherFolders);
$totalRows_rsCurrentOtherFolders = mysql_num_rows($rsCurrentOtherFolders);




$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Document"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  /><script src="/core/scripts/formUpload.js"></script>

<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
<?php if(!isset($row_rsDocPrefs['additionalfolders']) || $row_rsDocPrefs['additionalfolders']==0) {
	echo ".additionalfolders { display: none;}"; 
}?>

--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <section>
                  <div class="container pageBody documents">  
                   
                    
                    
                    
                    
                    <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="../index.php"><span itemprop="name">Documents</span></a>
       <meta itemprop="position" content="2" />
      </li>
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="../index.php?categoryID=<?php echo $row_rsDocument['documentcategoryID']; ?>"><span itemprop="name">
	  <?php echo isset($row_rsThisCategory['categoryname']) ? $row_rsThisCategory['categoryname'] : "Home folder"; ?></span></a> <meta itemprop="position" content="3" /></li>
      
       <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <span itemprop="item"><span itemprop="name">
	  Update Document</span></span> <meta itemprop="position" content="4" /></li>
      
      </ol></div></div>
      
      
                       <h1 title="<?php echo htmlentities($row_rsDocument['originalname'], ENT_COMPAT, "UTF-8"); ?>" data-toggle="tooltip"><?php echo htmlentities($row_rsDocument['documentname'], ENT_COMPAT, "UTF-8"); ?></h1>
    <?php // check access rights
			  if ($pageaccess && ($row_rsLoggedIn['usertypeID'] >= 9 || ($row_rsLoggedIn['ID'] == $row_rsDocument['userID'] && $row_rsFolders['accessID']==99) || ($row_rsFolders['active'] == 1 && $row_rsFolders['accessID'] <= $row_rsLoggedIn['usertypeID'] && ($row_rsDocument['lock'] !=1 || $row_rsLoggedIn['ID'] == $row_rsDocument['userID'])))) { // OK to access
			  // Is admin OR just me OR (is active, at same or lower access group  AND (is not locked OR is same user)) ?><?php require_once('../../core/includes/alert.inc.php'); ?>
   
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab" tabindex="0">Document</li>
      <li class="TabbedPanelsTab additionalfolders" tabindex="0">Additional folders</li>
      <li class="TabbedPanelsTab" tabindex="0">Information</li>
      <li class="TabbedPanelsTab" tabindex="0">Versions</li>
    </ul>
    <div class="TabbedPanelsContentGroup">
      <div class="TabbedPanelsContent">
        <table   class="form-table">
          <tr>
            <td align="right">Title:</td>
            <td ><span id="sprytextfield1">
              <input name="documentname" type="text"  id="documentname" class="form-control" value="<?php echo htmlentities($row_rsDocument['documentname'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="255" />
              <span class="textfieldRequiredMsg">A value is required.</span></span>
              </td>
          </tr>
          <tr>
            <td align="right">In folder: <span class="glyphicon glyphicon-folder-open"></span> </td>
            <td><select name="documentcategoryID"  id="documentcategoryID" class="form-control">
              
              <?php
do { $name =  $row_rsFolders['categoryname'];
$name .=  (isset($row_rsFolders['parentname'] ) && $row_rsFolders['parentname']!="Home") ? " [in ".$row_rsFolders['parentname']."]" : "";

if($totalRows_rsRegion>1) {
	$name = $row_rsFolders['site']." > ".$name;
}
/*$name =  (isset($row_rsFolders['grandparentname'] ) && $row_rsFolders['grandparentname']!="Home") ? $row_rsFolders['grandparentname'].">".$name : $name;*/
?>
              <option value="<?php echo $row_rsFolders['ID']?>"<?php if (!(strcmp($row_rsFolders['ID'], $row_rsDocument['documentcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $name; ?></option>
              <?php
} while ($row_rsFolders = mysql_fetch_assoc($rsFolders));
  $rows = mysql_num_rows($rsFolders);
  if($rows > 0) {
      mysql_data_seek($rsFolders, 0);
	  $row_rsFolders = mysql_fetch_assoc($rsFolders);
  }
?>
            </select>
              <a href="../folders/add_folder.php"></a></td>
          </tr>
          <tr>
            <td align="right" valign="top">Replace with: <span class="glyphicon glyphicon-duplicate"></span> </td>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo defined("MAX_UPLOAD") ? MAX_UPLOAD : "2000000"; ?>" />
              <input name="filename" type="file" class="fileinput" id="filename" size="20" />
              <input name="oldtype" type="hidden" id="oldtype" value="<?php echo $row_rsDocument['type']; ?>" />
              <input name="oldfilename" type="hidden" id="oldfilename" value="<?php echo $row_rsDocument['filename']; ?>" />
              <input name="uploadID" type="hidden" id="uploadID" value="<?php echo $row_rsDocument['uploadID']; ?>" /></td>
          </tr>
          <tr class="form-inline"  <?php echo (isset($documents_approval) && $documents_approval == true && $row_rsDocument['active'] == 0 && $_SESSION['MM_UserGroup'] <8 ) ? "style = 'display:none;'" : "";  ?>>
            <td class="text-nowrap text-right">Status:</td>
            <td><select name="status"  id="status" class="form-control">
              <?php
do {  
?>
              <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsDocument['active']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
              <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
            </select> <input <?php if (!(strcmp($row_rsDocument['lock'],1))) {echo "checked=\"checked\"";} ?> name="lock" type="checkbox" id="lock" value="1" />
              Locked</td>
          </tr>
          <tr>
            <td align="right"><input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
              <input name="uploaddatetime" type="hidden" id="uploaddatetime" value="<?php echo $row_rsDocument['uploaddatetime']; ?>"  />
              <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsDocument['userID']; ?>" />
              <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsDocument['ID']; ?>" /></td>
            <td><button type="submit" class="btn btn-primary">Save changes</button> <a href="/documents/view.php?documentID=<?php echo $row_rsDocument['ID']; ?>" target="_blank" rel="noopener" class="btn btn-default btn-secondary">Download</a></td>
          </tr>
        </table>
      </div>
      <div class="TabbedPanelsContent">
        <p>In addition to the document's main folder, you can add it to other folders. When you update the main document, it updates all. NOTE: Document will still honour permissions of it's main folder so ensure the new folder's permissions match.</p>
        <p>This document has not been added to any additional folders.</p>
        <p class="form-inline">Add to folder: <select name="documentincategoryID"  id="documentincategoryID" class="form-control">
              <option value="0" <?php if (!(strcmp(0, $row_rsDocument['documentcategoryID']))) {echo "selected=\"selected\"";} ?>>Root</option>
              <?php
do { $name =  $row_rsFolders['categoryname'];
$name .=  (isset($row_rsFolders['parentname'] ) && $row_rsFolders['parentname']!="Home") ? " [in ".$row_rsFolders['parentname']."]" : "";

?>
              <option value="<?php echo $row_rsFolders['ID']?>" ><?php echo $name; ?></option>
              <?php
} while ($row_rsFolders = mysql_fetch_assoc($rsFolders));
  $rows = mysql_num_rows($rsFolders);
  if($rows > 0) {
      mysql_data_seek($rsFolders, 0);
	  $row_rsFolders = mysql_fetch_assoc($rsFolders);
  }
?>
            </select> <input type="image" src="/core/images/icons/add.png" /><label><input name="makemain" type="checkbox" value="1" /> 
            Make main</label></p>
        <?php if ($totalRows_rsCurrentOtherFolders > 0) { // Show if recordset not empty ?>
  <table class="table table-hover">
    <tbody>
    <?php do { ?>
      <tr>
        
        <td><?php echo isset($row_rsCurrentOtherFolders['parentname']) ? htmlentities($row_rsCurrentOtherFolders['parentname'], ENT_COMPAT, "UTF-8").">" : "";  echo htmlentities($row_rsCurrentOtherFolders['categoryname'], ENT_COMPAT, "UTF-8"); ?></td>
        <td><a href="modify_document.php?documentID=<?php echo intval($_GET['documentID']) ?>&deletecategoryID=<?php echo $row_rsCurrentOtherFolders['ID']; ?>&token=<?php echo md5(PRIVATE_KEY.$row_rsCurrentOtherFolders['ID']); ?>" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
      </tr>
      <?php } while ($row_rsCurrentOtherFolders = mysql_fetch_assoc($rsCurrentOtherFolders)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
      </div>
      <div class="TabbedPanelsContent"> 
        <h3>
          <label for="description">Description/Keywords:</label></h3>
        <textarea name="description" id="description" class="form-control"><?php echo $row_rsDocument['description']; ?></textarea>
        <h3>Linking to this document:</h3>
            <p><label for="documentlink">If you want to link to this document, copy and paste the URL below into the link on the page.</label></p>
            <p><input name="documentlink" type="text"  id="documentlink" value="<?php echo getProtocol()."://". $_SERVER['HTTP_HOST']."/"."documents/view.php?documentID=".$row_rsDocument['ID']; ?>" size="80" maxlength="255"class="form-control" />
              
            </p>
            <h3>File information:</h3>
            <p>Originally uploaded by <?php echo $row_rsDocument['firstname']; ?> <?php echo $row_rsDocument['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsDocument['uploaddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsDocument['uploaddatetime'])); ?></p>
            <?php if(isset($row_rsLastModified['modifieddatetime'])) { ?><p>Last updated by <?php echo $row_rsLastModified['firstname']; ?> <?php echo $row_rsLastModified['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsLastModified['modifieddatetime'])); ?> on  <?php echo date('l jS F Y',strtotime($row_rsLastModified['modifieddatetime'])); ?></p><?php } ?>
            <p>File type: <?php echo $row_rsLastModified['type']; ?></p>
<h3>Allowed File Types:</h3>
            <p>You are only allowed to upload certain types of file as follows (extensions in brackets): Plain Text (TXT), Rich Text Format (RTF), 
      Word (DOC),  Powerpoint (PPT),  Excel (XLS), Acrobat (PDF), Flash (SWF), compressed (ZIP, SIT), JPEG (JPG), PNG (PNG) and GIF (GIF).  If in doubt, ZIP compress your file.</p>
    <p>Any file you upload must not exceed <?php  echo defined("MAX_UPLOAD") ? floor(MAX_UPLOAD/1000000) : "2"; ?>Mb in size. </p></div>
      <div class="TabbedPanelsContent">
        <?php if ($totalRows_rsVersions == 0) { // Show if recordset empty ?>
  <p>There are no previous versons of this document available.</p>
  <?php } // Show if recordset empty ?>
        <?php if ($totalRows_rsVersions > 0) { // Show if recordset not empty ?>
        <p>Previously saved versions of this document in chronological order:</p>
          <table  class="form-table">
            <?php do { ?>
              <tr class="docsItem">
                
              
                <td><a href="<?php echo str_replace(SITE_ROOT,"/",$row_rsVersions['newfilename']); ?>" class="document <?php echo substr(strrchr($row_rsVersions['filename'],'.'),1,3); ?>"><?php echo $row_rsVersions['filename']; ?></a></td>
                <td><em>&nbsp;saved by <?php echo $row_rsVersions['firstname']; ?> <?php echo $row_rsVersions['surname']; ?></em></td>
               <td><em>&nbsp;<?php echo date('d M Y H:i', strtotime($row_rsVersions['createddatetime'])); ?></em></td>
              </tr>
              <?php } while ($row_rsVersions = mysql_fetch_assoc($rsVersions)); ?>
          </table>
          <?php } // Show if recordset not empty ?>
    </div>
    </div>
  </div>
              
                
<input type="hidden" name="MM_insert" value="form1" />
              <input type="hidden" name="MM_update" value="form1" />
    </form>
           
            <?php } //end OK to access 
			else { // forbidden ?>
            <p class="alert alert-danger" role="alert">You do not have access to change this document.</p>
            <?php } ?>
            <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script></div></section>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFolders);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsDocument);

mysql_free_result($rsLastModified);

mysql_free_result($rsStatus);

mysql_free_result($rsVersions);

mysql_free_result($rsCurrentOtherFolders);

mysql_free_result($rsRegion);

mysql_free_result($rsDocPrefs);

mysql_free_result($rsCurrentFolder);
?>
