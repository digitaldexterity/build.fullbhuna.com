<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../directory/includes/directoryfunctions.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10,9,8,7";
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

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}
$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, users.regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$_POST['categoryID'] = (isset($_POST['categoryID']) && $_POST['categoryID']>0) ? $_POST['categoryID'] : 0;
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID =".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "add")) {
  $insertSQL = sprintf("INSERT INTO location (`public`, categoryID, locationname, `description`, address1, address2, address3, address4, address5, postcode, telephone1, telephone2, telephone3, fax, imageURL, mapURL, locationURL, createdbyID, createddatetime, locationemail) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString(isset($_POST['public']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['locationname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['telephone1'], "text"),
                       GetSQLValueString($_POST['telephone2'], "text"),
                       GetSQLValueString($_POST['telephone3'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['mapURL'], "text"),
                       GetSQLValueString($_POST['locationURL'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['locationemail'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
  
  if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "add")) { // if insert
  $locationID = mysql_insert_id();
  if(isset($_POST['directoryID']) && intval($_POST['directoryID']) >0) { // is directory location
  	addLocationToDirectory($_POST['directoryID'], $locationID, $row_rsLoggedIn['ID']);
	if(isset($_POST['registeredaddress'])) {
	  $update = "UPDATE directory SET registeredaddressID = ".$locationID." WHERE ID = ".intval($_POST['directoryID']);
	  mysql_query($update, $aquiescedb) or die(mysql_error());
  	}
 
  } // end is directory location
  
  
  
  
  if(isset($_GET['userID']) && intval($_GET['userID']) >0) { // is user
  $relationshipID = isset($_POST['relationshipID']) ? $_POST['relationshipID'] : "";
	  addUserToLocation(intval($_GET['userID']), $locationID, $row_rsLoggedIn['ID'], false, false, $relationshipID);
  }
  $returnURL = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php?orderby=datetime";
  if(isset($row_rsPreferences['googlemapsAPI']) && trim($row_rsPreferences['googlemapsAPI'])!="") {
  	// if we have a maps key go to add map location
  	$insertGoTo =  "modify_location.php?locationID=".$locationID."&defaulTab=1&returnURL=".urlencode($returnURL);
  } else {
	  $insertGoTo  = $returnURL;
  }

  header(sprintf("Location: %s", $insertGoTo));exit;
} // end insert



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAreas = "SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 ORDER BY categoryname ASC";
$rsAreas = mysql_query($query_rsAreas, $aquiescedb) or die(mysql_error());
$row_rsAreas = mysql_fetch_assoc($rsAreas);
$totalRows_rsAreas = mysql_num_rows($rsAreas);

$colname_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT name FROM directory WHERE ID = %s", GetSQLValueString($colname_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs WHERE ID = ".$regionID;
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRelationships = "SELECT ID, relationship FROM locationuserrelationship ORDER BY relationship ASC";
$rsRelationships = mysql_query($query_rsRelationships, $aquiescedb) or die(mysql_error());
$row_rsRelationships = mysql_fetch_assoc($rsRelationships);
$totalRows_rsRelationships = mysql_num_rows($rsRelationships);

$colname_rsThisUser = "-1";
if (isset($_GET['userID'])) {
  $colname_rsThisUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUser = sprintf("SELECT ID, firstname, surname FROM users WHERE ID = %s", GetSQLValueString($colname_rsThisUser, "int"));
$rsThisUser = mysql_query($query_rsThisUser, $aquiescedb) or die(mysql_error());
$row_rsThisUser = mysql_fetch_assoc($rsThisUser);
$totalRows_rsThisUser = mysql_num_rows($rsThisUser);



$_GET['categoryID'] = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0;

?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add ".ucwords($row_rsLocationPrefs['locationdescriptor']); echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?><style>.areas { display:none; }</style><?php } ?>
<script src="../../core/scripts/formUpload.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<style><!--
<?php if(!isset($row_rsThisDirectory['name'])) { 
echo "#row_registered_address, .entrydate { display: none; } "; 
} ?>
<?php if(!isset($row_rsThisUser['ID']) || $totalRows_rsRelationships==0) { 
echo ".relationship { display: none; } "; 
} ?>
--></style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page location">
          <h1><i class="glyphicon glyphicon-flag"></i> Add <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?><?php echo isset($row_rsThisDirectory['name']) ? " for ".$row_rsThisDirectory['name'] : ""; ?></h1>
        <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
       
          <?php } ?>
     <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="add" id="add">
     
              
                <table  class="form-table">
                  <tr class="areas">
                    <td class="text-right">Category:</td>
                    <td class="form-inline"><select name="categoryID"  id="categoryID" class="form-contol">
                      <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>None specified</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsAreas['ID']?>"<?php if (!(strcmp($row_rsAreas['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAreas['categoryname']?></option>
                      <?php
} while ($row_rsAreas = mysql_fetch_assoc($rsAreas));
  $rows = mysql_num_rows($rsAreas);
  if($rows > 0) {
      mysql_data_seek($rsAreas, 0);
	  $row_rsAreas = mysql_fetch_assoc($rsAreas);
  }
?>
                    </select>
                      <a href="category/index.php">Manage Categories</a></td>
                  </tr>
                  <tr class="relationship">
                    <td class="text-right"><label for="relationshipID">Relationship to <?php echo $row_rsThisUser['firstname']; ?> <?php echo $row_rsThisUser['surname']; ?>:</label></td>
                    <td class="form-inline">
                      <select name="relationshipID" id="relationshipID" class="form-control">
                        <option value="" <?php if (!(strcmp("", @$_GET['relationshipID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsRelationships['ID'];  ?>"<?php if (!(strcmp($row_rsRelationships['ID'], @$_GET['relationshipID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRelationships['relationship']?></option>
                        <?php
} while ($row_rsRelationships = mysql_fetch_assoc($rsRelationships));
  $rows = mysql_num_rows($rsRelationships);
  if($rows > 0) {
      mysql_data_seek($rsRelationships, 0);
	  $row_rsRelationships = mysql_fetch_assoc($rsRelationships);
  }
?>
                    </select> 
                      <a href="relationships/index.php">Manage Relationships</a></td>
                  </tr>
                  <tr class="entrydate">
                    <td class="text-right">Entry date (optional):</td>
                    <td><input name="entrydate" type="hidden" id="entrydate" value="<?php $inputname = "entrydate"; $setvalue= isset($_POST['entrydate']) ? htmlentities($_POST['entrydate']) : ""; echo $setvalue; ?>" />
            <?php require('../../core/includes/datetimeinput.inc.php'); ?></td>
                  </tr>
                  <tr>
                    <td class="text-right"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Name: </td>
                    <td><span id="sprytextfield3">
                      <input name="locationname" type="text"  id="locationname" value="<?php echo isset($_REQUEST['locationname']) ? htmlentities($_REQUEST['locationname']) : ""; ?>" size="40" maxlength="50" class="form-control" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                  
                  <tr>
                    <td class="text-right">Address:</td>
                    <td><input name="address1" type="text"  id="address1" value="<?php echo isset($_REQUEST['address1']) ? htmlentities($_REQUEST['address1']) : ""; ?>" size="40" maxlength="50" onBlur="setAddress()"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address2" type="text"  id="address2" value="<?php echo isset($_REQUEST['address2']) ? htmlentities($_REQUEST['address2']) : ""; ?>" size="40" maxlength="50" onBlur="setAddress()"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address3" type="text"  id="address3" value="<?php echo isset($_REQUEST['address3']) ? htmlentities($_REQUEST['address3']) : ""; ?>" size="40" maxlength="50" onBlur="setAddress()"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address4" type="text"  id="address4" value="<?php echo isset($_REQUEST['address4']) ? htmlentities($_REQUEST['address4']) : ""; ?>" size="40" maxlength="50" onBlur="setAddress()"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address5" type="text"  id="address5" value="<?php echo isset($_REQUEST['address5']) ? htmlentities($_REQUEST['address5']) : ""; ?>" size="40" maxlength="50" onBlur="setAddress()"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">Postcode: </td>
                    <td><input name="postcode" type="text"  id="postcode" size="20" maxlength="10" onBlur="setAddress()" value="<?php echo isset($_REQUEST['postcode']) ? htmlentities($_REQUEST['postcode']) : ""; ?>" class="form-control" /><?php if(trim($row_rsLocationPrefs['postcodecheckerkey'])!="") { ?><script src="//services.postcodeanywhere.co.uk/popups/javascript.aspx?account_code=indiv46069&license_key=<?php echo $row_rsLocationPrefs['postcodecheckerkey']; ?>"></script><?php } ?></td>
                  </tr>
                  <tr>
                    <td class="text-right">Telephone:</td>
                    <td><input name="telephone1" type="text"  id="telephone1" size="40" maxlength="50" value="<?php echo isset($_REQUEST['telephone1']) ? htmlentities($_REQUEST['telephone1']) : ""; ?>" class="form-control" /></td>
                  </tr>
                   <tr>
                    <td class="text-right">Telephone 2:</td>
                    <td><input name="telephone2" type="text"  id="telephone2" size="40" maxlength="50" value="<?php echo isset($_REQUEST['telephone2']) ? htmlentities($_REQUEST['telephone2']) : ""; ?>" class="form-control" /></td>
                  </tr>
                   <tr>
                    <td class="text-right">Telephone 3:</td>
                    <td><input name="telephone3" type="text"  id="telephone3" size="40" maxlength="50" value="<?php echo isset($_REQUEST['telephone3']) ? htmlentities($_REQUEST['telephone3']) : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">Fax:</td>
                    <td><input name="fax" type="text"  id="fax" size="40" maxlength="20" value="<?php echo isset($_REQUEST['fax']) ? htmlentities($_REQUEST['fax']) : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right" ><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> email:</td>
                    <td><span id="sprytextfield2">
                      <input name="locationemail" type="text"  id="locationemail" size="40" maxlength="50" value="<?php echo isset($_REQUEST['locationemail']) ? htmlentities($_REQUEST['locationemail']) : ""; ?>"  class="form-control"/>
                    <span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                  </tr>
                  <tr id="row_url">
                    <td class="text-right" >Web site:</td>
                    <td>
                    <input name="locationURL" type="text"  id="locationURL" size="40" maxlength="100" value="<?php echo isset($_REQUEST['locationURL']) ? htmlentities($_REQUEST['locationURL']) : ""; ?>" placeholder="http://"  class="form-control"/>
</td>
                  </tr><tr>
                    <td class="text-right" valign="top"><?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Description: </td>
                    <td><textarea name="description" cols="40" rows="4" id="description"  class="form-control"><?php echo isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) : ""; ?></textarea></td>
                  </tr>
                  <tr class="upload" id="row_upload">
                    <td class="text-nowrap text-right">                      Image:</td>
                    <td><input name="filename" type="file" class="fileinput" id="filename" size="20" /></td>
                  </tr>
                  <tr id="row_public">
                    <td class="text-right"><input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                    Public:                      </td>
                    <td><input name="public" type="checkbox" id="public" value="1" <?php if(!isset($_GET['userID'])) { echo"checked=\"checked\""; } ?> /></td>
                  </tr>
                  <tr id="row_registered_address">
                    <td class="text-right"><label for="registeredaddress">Company registered address:</label></td>
                    <td><input type="checkbox" name="registeredaddress" id="registeredaddress" <?php if(isset($_REQUEST['registeredaddress'])) { echo "checked=\"checked\""; } ?>/>
                    </td>
                  </tr>
                </table>
        <input type="hidden" name="MM_insert" value="add" />
                <span class="text-nowrap text-right">
                <input name="imageURL" type="hidden" id="imageURL" />
                </span>
                <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
                <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
                          <input name="directoryID" type="hidden" id="directoryID" value="<?php echo intval($_GET['directoryID']); ?>" />
            
  
    
           
       <button type="submit" class = "btn btn-primary" >Add <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?></button></form>
<script>
<!--
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email", {isRequired:false});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
//-->
</script></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsAreas);

mysql_free_result($rsThisDirectory);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsRelationships);

mysql_free_result($rsThisUser);

mysql_free_result($rsLoggedIn);
?>
