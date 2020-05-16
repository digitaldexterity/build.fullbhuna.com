<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
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
<?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
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

if (isset($_POST["MM_update"]) && $_POST["MM_update"] == "form1") {
	$_POST['url'] = ($_POST['url'] == "http://") ? "" : $_POST['url'];
	if($_POST['url']!="" && substr($_POST['url'],0,7) !="http://")  {
					  unset($_POST["MM_update"]);
					  $submit_error = "Invalid web site URL. Must start with http://";					  
					  }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if(isset($_POST['subcategoryID']) && $_POST['subcategoryID'] > 0) { $_POST['categoryID'] = $_POST['subcategoryID']; }


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE directory SET name=%s, `description`=%s, address1=%s, address2=%s, address3=%s, address4=%s, address5=%s, postcode=%s, telephone=%s, fax=%s, mobile=%s, imageURL=%s, email=%s, url=%s, modifiedbyID=%s, modifieddatetime=%s, directorytype=%s, `public`=%s WHERE ID=%s",
                       GetSQLValueString($_POST['name'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['telephone'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['mobile'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['url'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['directorytype'], "int"),
                       GetSQLValueString(isset($_POST['public']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	$delete = "DELETE FROM directoryinarea WHERE directoryID = ". GetSQLValueString($_POST['ID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	if(isset($_POST['directoryarea'])) {
		 foreach($_POST['directoryarea'] as $value) {
	$insert = "INSERT INTO directoryinarea (directoryID, directoryareaID, createdbyID, createddatetime) VALUES (". GetSQLValueString($_POST['ID'], "int").",".$value.",".GetSQLValueString($_POST['modifiedbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	
}
	}

 $updateGoTo = (isset($_REQUEST['returnURL']) && $_REQUEST['returnURL'] !="") ? $_REQUEST['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  $updateGoTo = removeQueryVarFromURL($updateGoTo,"returnURL");
  header(sprintf("Location: %s", $updateGoTo));exit();
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

$varRegionID_rsParentCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsParentCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsParentCategory = sprintf("SELECT ID, description FROM directorycategory WHERE (directorycategory.regionID = %s OR directorycategory.regionID=0) AND directorycategory.statusID = 1 AND directorycategory.subCatOfID = 0 ORDER BY description ASC", GetSQLValueString($varRegionID_rsParentCategory, "int"));
$rsParentCategory = mysql_query($query_rsParentCategory, $aquiescedb) or die(mysql_error());
$row_rsParentCategory = mysql_fetch_assoc($rsParentCategory);
$totalRows_rsParentCategory = mysql_num_rows($rsParentCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status ORDER BY ID ASC";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.*, directorycategory.subCatOfID, users.firstname, users.surname FROM directory LEFT JOIN users ON (directory.createdbyID = users.ID) LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$varOrgID_rsLastModified = "-1";
if (isset($_GET['directoryID'])) {
  $varOrgID_rsLastModified = (get_magic_quotes_gpc()) ? $_GET['directoryID'] : addslashes($_GET['directoryID']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastModified = sprintf("SELECT directory.modifieddatetime, users.firstname, users.surname FROM directory LEFT JOIN users ON (directory.modifiedbyID = users.ID)  WHERE directory.ID = '%s'", $varOrgID_rsLastModified);
$rsLastModified = mysql_query($query_rsLastModified, $aquiescedb) or die(mysql_error());
$row_rsLastModified = mysql_fetch_assoc($rsLastModified);
$totalRows_rsLastModified = mysql_num_rows($rsLastModified);

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

$varDirectoryID_rsDirectoryAreas = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryAreas = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryAreas = sprintf("SELECT directoryarea.ID, directoryarea.areaname, directoryinarea.directoryareaID FROM directoryarea LEFT JOIN directoryinarea ON (directoryarea.ID = directoryinarea.directoryareaID AND directoryinarea.directoryID = %s) WHERE directoryarea.statusID ORDER BY directoryarea.areaname", GetSQLValueString($varDirectoryID_rsDirectoryAreas, "int"));
$rsDirectoryAreas = mysql_query($query_rsDirectoryAreas, $aquiescedb) or die(mysql_error());
$row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas);
$totalRows_rsDirectoryAreas = mysql_num_rows($rsDirectoryAreas);
?>
<?php 
// set initial parameters for select menus
$parentID = ($row_rsDirectory['subCatOfID']>0) ? $row_rsDirectory['subCatOfID'] : $row_rsDirectory['categoryID'];
$categoryID = ($row_rsDirectory['subCatOfID']>0) ? $row_rsDirectory['categoryID'] : 0;
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Update My Organisation - ".$row_rsDirectory['name']; echo " - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" /><?php if (!isset($allowlocalwebpage) || $allowlocalwebpage == false) { echo"
<style>
#linkWebPage { display:none; } 
</style>";
} ?>
<style><!--
<?php if ($totalRows_rsDirectoryAreas < 1) { echo ".directoryareas { display:none; }"; }  ?>
--></style>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<?php $googlemapsAPI =isset($googlemapsAPI) ? $googlemapsAPI : $row_rsPreferences['googlemapsAPI'];
if($googlemapsAPI=="") {  echo "<style> #linkMap { display:none; } </style>" ; } ?>
<script src="/core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationRadio.js"></script>
<link href="../../SpryAssets/SpryValidationRadio.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
     
	  <h1 class="directoryheader"><?php echo htmlentities($row_rsDirectory['name'], ENT_COMPAT, "UTF-8"); ?> details</h1> 
	  <?php if ($totalRows_rsIsAuthorised >0 || $row_rsLoggedIn['usertypeID'] >=8) { //authorsied to access ?>
	  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
	    <li><a href="add_directory.php" ><i class="glyphicon glyphicon-plus-sign"></i> New</a></li>
	    <li id="linkContacts"><a href="contacts/index.php?directoryID=<?php echo intval($_GET['directoryID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Contacts</a></li>
	    <li id="linkLocations"><a href="locations/index.php?directoryID=<?php echo intval($_GET['directoryID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Locations</a></li>
	    <li id="linkMap"><a href="map.php?directoryID=<?php echo intval($_GET['directoryID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Map </a></li>
	    <li id="linkWebPage"><a href="/sites/members/edit_web_page.php?directoryID=<?php echo intval($_GET['directoryID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Web Page</a></li>
	    <li><a href="gallery/index.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Photos</a></li>
	    
      </ul></div></nav>
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
        <table class="form-table"> <tr>
            <td class="text-nowrap text-right">Name:</td>
            <td><span id="sprytextfield1">
              <input type="text"  name="name" value="<?php echo htmlentities($row_rsDirectory['name'], ENT_COMPAT, "UTF-8"); ?>"  class="form-control" maxlength="255" />
            <span class="textfieldRequiredMsg">A value is required.</span></span></td>
          </tr> <tr>
            <td class="text-nowrap text-right top directorytypes">Type:
             
            </td>
            <td><span id="spryradio1"><label>
              <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="1" id="directorytype_1" />
              Ltd</label>
              <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="2" id="directorytype_2" />
                LLP</label>
              <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="3" id="directorytype_3" />
                Sole trader</label>
              <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="4" id="directorytype_4" />
                Charity</label>
              <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"5"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="5" id="directorytype_5" />
                Private </label>
              <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"6"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="6" id="directorytype_6" />
            Social Enterprise</label>
             <label>
                <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"7"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="7" id="directorytype_7" />
            Public Sector</label>
            
            <input <?php if (!(strcmp($row_rsDirectory['directorytype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="directorytype" value="0" id="directorytype_0" />
            Other</label>
            
            <br><span class="radioRequiredMsg">Please make a selection.</span></span></td>
          </tr>
          <tr class="directoryareas">
            <td class="text-nowrap text-right top">Areas covered:</td>
            <td><?php do { ?>
              <label>
                <input name="directoryarea[<?php echo $row_rsDirectoryAreas['ID']; ?>]" type="checkbox" value="<?php echo $row_rsDirectoryAreas['ID']; ?>" <?php if($row_rsDirectoryAreas['directoryareaID']) { echo "checked=\"checked\""; } ?>>
            <?php echo $row_rsDirectoryAreas['areaname']; ?></label>
              &nbsp;&nbsp;&nbsp;
              <?php } while ($row_rsDirectoryAreas = mysql_fetch_assoc($rsDirectoryAreas)); ?></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td><textarea name="description" id="description" cols="50" rows="5" class="form-control" ><?php echo htmlentities($row_rsDirectory['description'], ENT_COMPAT, "UTF-8"); ?></textarea></td>
          </tr> <tr>
            <td class="nowrap text-right top">Main address:</td>
            <td><input name="address1" type="text"  id="address1" value="<?php echo htmlentities($row_rsDirectory['address1'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right top">&nbsp;</td>
            <td><input name="address2" type="text"  id="address2" value="<?php echo htmlentities($row_rsDirectory['address2'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50"  class="form-control" /></td>
          </tr> <tr>
            <td class="nowrap text-right top">&nbsp;</td>
            <td><input name="address3" type="text"  id="address3" value="<?php echo htmlentities($row_rsDirectory['address3'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50"  class="form-control" /></td>
          </tr> <tr>
            <td class="nowrap text-right top">&nbsp;</td>
            <td><input name="address4" type="text"  id="address4" value="<?php echo htmlentities($row_rsDirectory['address4'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right top">&nbsp;</td>
            <td><input name="address5" type="text"  id="address5" value="<?php echo htmlentities($row_rsDirectory['address5'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right">Postcode:</td>
            <td><input name="postcode" type="text"  value="<?php echo htmlentities($row_rsDirectory['postcode'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="10"  class="form-control" /></td>
          </tr> <tr>
            <td class="nowrap text-right">Telephone:</td>
            <td><input name="telephone" type="text"  id="telephone" value="<?php echo htmlentities($row_rsDirectory['telephone'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="20" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right">Mobile:</td>
            <td><input name="mobile" type="text"  id="mobile" value="<?php echo htmlentities($row_rsDirectory['mobile'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="20" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right">Fax:</td>
            <td><input name="fax" type="text"  id="fax" value="<?php echo htmlentities($row_rsDirectory['fax'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="20" class="form-control"  /></td>
          </tr> <tr>
            <td class="nowrap text-right">Organisation email:</td>
            <td><input name="email" type="email" multiple value="<?php echo htmlentities($row_rsDirectory['email'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="100"  class="form-control" /></td>
          </tr> <tr>
            <td class="nowrap text-right">Image:</td>
            <td><?php if (isset($row_rsDirectory['imageURL'])) { ?>
              <img src="<?php echo getImageURL($row_rsDirectory['imageURL'],"medium"); ?>" /><br />
              <input name="noImage" type="checkbox" value="1" />
              <?php } else { ?>
              No image associated with this organisation.
              <?php } ?>
              <span class="upload"><br />
              Add/change image below:<br />
            <input name="filename" type="file" id="filename" size="20" /></span>            </td>
          </tr> <tr>
            <td class="nowrap text-right">External web site :</td>
            <td>
            <input name="url" type="text"  value="<?php echo htmlentities($row_rsDirectory['url'], ENT_COMPAT, "UTF-8"); ?>" size="32" maxlength="100"  class="form-control" />
</td>
          </tr> <tr>
            <td class="nowrap text-right">&nbsp;</td>
            <td><label>
<input <?php if (!(strcmp($row_rsDirectory['public'],1))) {echo "checked=\"checked\"";} ?> name="public" type="checkbox" id="public" value="1">
            I am happy for this information to be publicly available on the web site</label></td>
          </tr> <tr>
            <td class="nowrap text-right">&nbsp;</td>
            <td><div><button type="submit" class="btn btn-primary" >Save changes</button></div></td>
          </tr>
        </table>
    <input type="hidden" name="ID" value="<?php echo $row_rsDirectory['ID']; ?>" />
        <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="ID" value="<?php echo $row_rsDirectory['ID']; ?>" />
        <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
        <input name="imageURL" type="hidden" value="<?php echo $row_rsDirectory['imageURL']; ?>" />
    </form>

      <p class="text-muted">Originally created by <?php echo $row_rsDirectory['firstname']; ?> <?php echo $row_rsDirectory['surname']; ?> at <?php echo date('g:ia',strtotime( $row_rsDirectory['createddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsDirectory['createddatetime'])); ?></p>
      <?php if(isset($row_rsLastModified['modifieddatetime'])) { ?>
  <p class="text-muted">Last updated by <?php echo $row_rsLastModified['firstname']; ?> <?php echo $row_rsLastModified['surname']; ?> at <?php echo date('g:ia',strtotime($row_rsLastModified['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsLastModified['modifieddatetime'])); ?></p>
    <?php } ?>
      <?php } //end authorised
	  else { ?>
      <p class="alert alert-danger" role="alert">You are not authorised to edit this entry.</p>
      <?php } ?><script>
	  getData('/directory/ajax/createSubCatSelect.php?parentCatID='+document.getElementById('categoryID').value+'&categoryID=<?php echo $row_rsDirectory['categoryID']; ?>','subCat');
	  </script>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
//-->
      </script>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsParentCategory);

mysql_free_result($rsStatus);

mysql_free_result($rsDirectory);

mysql_free_result($rsLastModified);

mysql_free_result($rsDirectoryUser);

mysql_free_result($rsIsAuthorised);

mysql_free_result($rsPreferences);

mysql_free_result($rsDirectoryAreas);
?>
