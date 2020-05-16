<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/autolinks.inc.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../includes/documentfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$varCategoryID_rsInFolder = "0";
if (isset($_REQUEST['categoryID'])) {
  $varCategoryID_rsInFolder = $_REQUEST['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsInFolder = sprintf("SELECT * FROM documentcategory WHERE documentcategory.ID = %s", GetSQLValueString($varCategoryID_rsInFolder, "int"));
$rsInFolder = mysql_query($query_rsInFolder, $aquiescedb) or die(mysql_error());
$row_rsInFolder = mysql_fetch_assoc($rsInFolder);
$totalRows_rsInFolder = mysql_num_rows($rsInFolder);

$varUserGroup_rsAddToFolders = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsAddToFolders = $_SESSION['MM_UserGroup'];
}
$varRegionID_rsAddToFolders = "1";
if (isset($regionID)) {
  $varRegionID_rsAddToFolders = $regionID;
}
$varUserID_rsAddToFolders = "-1";
if (isset($row_rsLoggedIn['ID'])) {
  $varUserID_rsAddToFolders = $row_rsLoggedIn['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAddToFolders = sprintf("SELECT documentcategory.ID, documentcategory.categoryname, subcat.ID AS subcat FROM documentcategory LEFT JOIN documentcategory AS subcat ON (subcat.subcatofID = documentcategory.ID) WHERE documentcategory.active = 1 AND (documentcategory.regionID=0 OR documentcategory.regionID = %s) AND (documentcategory.writeaccess <= %s OR (documentcategory.addedbyID = %s AND documentcategory.writeaccess = 99)) GROUP BY documentcategory.ID ORDER BY categoryname ASC ", GetSQLValueString($varRegionID_rsAddToFolders, "int"),GetSQLValueString($varUserGroup_rsAddToFolders, "int"),GetSQLValueString($varUserID_rsAddToFolders, "int"));
$rsAddToFolders = mysql_query($query_rsAddToFolders, $aquiescedb) or die(mysql_error());
$row_rsAddToFolders = mysql_fetch_assoc($rsAddToFolders);
$totalRows_rsAddToFolders = mysql_num_rows($rsAddToFolders);

if(isset($_POST['createddatetime'])) { // post
	if($_POST['categoryID'] == "new") { // new category
	$subcatofID = isset($_GET['categoryID']) ? intval($_GET['categoryID']): 0;
		$insert = "INSERT INTO documentcategory (accessID, active, addedbyID, categoryname, subcatofID, createddatetime) VALUES (0,1,".GetSQLValueString($_POST['postedbyID'], "int").",".GetSQLValueString($_POST['newcategory'], "text").",".$subcatofID.", NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$categoryID = mysql_insert_id();
		$access = true;
	} else {
		$categoryID = $_POST['categoryID'];
		$access = (($row_rsLoggedIn['usertypeID']>=$row_rsInFolder['writeaccess'] && ($row_rsInFolder['groupwriteID']==0 || userinGroup($row_rsInFolder['groupwriteID']))) || $row_rsLoggedIn['usertypeID']>=9 || ($row_rsInFolder['writeaccess']==99 && $row_rsInFolder['addedbyID'] == $row_rsLoggedIn['ID']));
	}
	if($access) {
		if(isset($_POST['document'])) {
			mysql_select_db($database_aquiescedb, $aquiescedb);
			
			foreach($_POST['document'] as $key => $value) {
				
				$lock = isset($_POST['lock'][$key]) ? 1 : 0;
				$_POST['uploadID'] = 0;// need to get the real value here...
				
				$select = "SELECT ID, mimetype FROM uploads WHERE newfilename LIKE ".GetSQLValueString(SITE_ROOT."Uploads/".$value, "text")." LIMIT 1"; 
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$upload = mysql_fetch_assoc($result);
				
				
				addDocument($_POST['title'][$key], $categoryID, $upload['ID'], $_POST['statusID'], $value,  $_POST['postedbyID'], $upload['mimetype'], $lock);
						  
				
			}
			session_regenerate_id(); // so that folder is no longer used
			header("location: /documents/index.php?categoryID=".$categoryID); exit;
		} // is doc
	} else { // has access
		$msg = "You do not have permission to add documents to this folder.";
	}
	
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Review your documents"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../photos/css/defaultGallery.css" rel="stylesheet"  />
<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<script>
addListener("load", toggleCategory);
function toggleCategory() {
	if(document.getElementById('categoryID').value == 'new') {
		document.getElementById('newcategory').style.display = 'inline';
	} else {
		document.getElementById('newcategory').style.display = 'none';
	}
}</script>
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<link href="../css/documentsDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
         <div class="container pageBody documents">
            <h1 class="documentheader">Review your documents</h1><?php require_once('../../core/includes/alert.inc.php'); ?>
            <p>Upload complete. Check over your files, add any descriptions and confirm a folder to add them to.</p>
            <form action="review.php" method="post" name="form1" id="form1">
              <?php $directory = "users/".$_SESSION['MM_Username']."/".session_id()."/"; 
  $path = UPLOAD_ROOT.$directory;  // removed dots 
  $prefix = date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR;
  $count = 0;
  if(is_dir($path.$prefix)) {
	$files = scandir($path.$prefix);
	if(count($files)>1) { 
		foreach($files as $key => $value) { 
			if(substr($value, 0,1) !="." && $value !="index.htm") { // not dot file or security index.htm file
				$file = $path.$prefix.$value; 
	 			
					
					$count ++; ?>
              <div class="documentItem">
                <input name="document[<?php echo $key; ?>]" type="checkbox" id="document<?php echo $key; ?>" value="<?php echo $directory.$prefix.$value; ?>" checked="checked"  /> File: <?php echo  substr($value,9);// remove random prefix ?>
                <div class="documentDetails form-inline" style="margin: 2px 0 10px 0">
                 
                  <label  for="title[<?php echo $key; ?>]">Description:</label><input name="title[<?php echo $key; ?>]" type="text" id="title<?php echo $key; ?>" value="<?php echo   substr($value,9); ?>" size="50" maxlength="255" class="form-control" /> <label><input name="lock[<?php echo $key; ?>]" type="checkbox" id="lock<?php echo $key; ?>"   /> Lock</label>
                </div>
              </div>
              <?php
			} // is not dot
		} // end for each
	} // is files
  } // is path
  if($count>0) { ?>
              <p class="form-inline"><label>Add selected documents to: <span id="spryselect1">
                
                  <select name="categoryID" id="categoryID" onchange="toggleCategory()" class="form-control">
                    <option value="" <?php if (!(strcmp("", @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                    <option value="new" <?php if (!(strcmp("new", @$_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>New folder...</option>
                    
                    <?php
		do {  
?>
                    <option value="<?php echo $row_rsAddToFolders['ID']?>"<?php if (!(strcmp($row_rsAddToFolders['ID'], @$_GET['categoryID']))) echo "selected=\"selected\""; ?>><?php echo $row_rsAddToFolders['categoryname']?></option>
                    <?php
} while ($row_rsAddToFolders = mysql_fetch_assoc($rsAddToFolders));
  $rows = mysql_num_rows($rsAddToFolders);
  if($rows > 0) {
      mysql_data_seek($rsAddToFolders, 0);
	  $row_rsAddToFolders = mysql_fetch_assoc($rsAddToFolders);
  } // end if
?>
                  </select>
             
                <span class="selectRequiredMsg">Please select an item.</span></span> </label>
                <input name="newcategory" type="text" id="newcategory" size="30" maxlength="50" class="form-control" />
              </p>
              <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
              <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <p class="form-inline">
                <label>Initial status:
                  <select name="statusID" id="statusID" class="form-control">
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], 1))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
                    <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  } // end if
?>
                  </select>
                </label>
              </p>
             
              <p>
                <button type="submit" class="btn btn-primary" >Add to folder...</button>
              </p>
              <?php } else { ?>
              <p class="alert warning alert-warning" role="alert">No files are available to add to a folder. <a href="add_documents.php">Please try again</a>.</p>
              <?php }  ?>
            </form>
    </div>
          <script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
  </script>
          <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMediaPrefs);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsInFolder);

mysql_free_result($rsAddToFolders);
?>
