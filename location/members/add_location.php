<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../directory/includes/directoryfunctions.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php

$regionID = (isset($regionID)  && $regionID>0) ? intval($regionID ): 1;


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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID=".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "add")) {
  $insertSQL = sprintf("INSERT INTO location (locationname, `description`, postcode, fax, imageURL, createdbyID, createddatetime, categoryID, address1, address2, address3, address4, address5, telephone1, telephone2, userID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['locationname'], "text"),
                       GetSQLValueString($_POST['locationdescription'], "text"),
                       GetSQLValueString($_POST['postcode'], "text"),
                       GetSQLValueString($_POST['fax'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['address1'], "text"),
                       GetSQLValueString($_POST['address2'], "text"),
                       GetSQLValueString($_POST['address3'], "text"),
                       GetSQLValueString($_POST['address4'], "text"),
                       GetSQLValueString($_POST['address5'], "text"),
                       GetSQLValueString($_POST['telephone1'], "text"),
                       GetSQLValueString($_POST['telephone2'], "text"),
                       GetSQLValueString($_POST['userID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
  
  if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "add")) { // if insert
   $insertID = mysql_insert_id();
  // is it a user address addition? If so, check if default exists and if not make this it
  if($_POST['userID']>0) { // user address
	  $select = "SELECT defaultaddressID FROM users WHERE ID = ".GetSQLValueString($_POST['userID'], "int");
	   $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	   $row = mysql_fetch_assoc($result);
	   if($row['defaultaddressID']<1) { // none set so update default
	   $update = "UPDATE users SET defaultaddressID = ". $insertID." WHERE ID = ".GetSQLValueString($_POST['userID'], "int");
	    $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	   } // end update default
	   
	   if($row_rsPreferences['userupdatealert']==1) {
			$to = $row_rsPreferences['contactemail'];
			$subject = $site_name." user profile update";
			$message = $_POST['firstname']." ".$_POST['surname']." has added an address.\n\n";
			$message .= "View their user profile here:\n\n";
			$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/members/admin/modify_user.php?userID=".intval($_POST['userID']);			
			sendMail($to,$subject,$message);
		}
		
		
		
  } // end user address
  
  if(isset($_POST['directoryID']) && intval($_POST['directoryID'])>0) { // is directory location
  	addLocationToDirectory($_POST['directoryID'], $insertID, $_POST['createdbyID']);	
  	
  }
  
  $referer = explode("?", $_POST['referer']);
 
  $insertGoTo = $referer[0] . "?locationID=". $insertID;$queryString = "";
  if (isset($referer[1])) { // if referer query string add to end removing old locID
  
 
  $params = explode("&", $referer[1]);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "locationID") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString = "&" . htmlentities(implode("&", $newParams));
  }
    
    $insertGoTo .= $queryString;	
  } // end is prev query string 
  if(isset($row_rsPreferences['googlemapsAPI'])) { // gooogle maps exist
  $insertGoTo = "update_location.php?defaultTab=1&locationID=".$insertID."&returnURL=".urlencode($insertGoTo);
  $insertGoTo .= isset($_GET['useraddress']) ? "&useraddress=true" : "";
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
} // end insert


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAreas = "SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 ORDER BY categoryname ASC";
$rsAreas = mysql_query($query_rsAreas, $aquiescedb) or die(mysql_error());
$row_rsAreas = mysql_fetch_assoc($rsAreas);
$totalRows_rsAreas = mysql_num_rows($rsAreas);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID,  users.regionID, CONCAT(users.firstname,' ',users.surname) AS fullname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT fullname, regionID, countries.ID FROM countries WHERE statusID = 1 ORDER BY ordernum ASC, fullname ASC";
$rsCountries = mysql_query($query_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);
$totalRows_rsCountries = mysql_num_rows($rsCountries);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);
?>
<?php $_GET['categoryID'] = isset($_GET['categoryID']) ? $_GET['categoryID'] : 0; ?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add address location"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if ($row_rsPreferences['uselocationcategory']!=1) { ?><style>.areas { display:none; }</style><?php } ?>
<?php if(isset($_GET['useraddress'])) { // user address so hide some fields
echo "<style>.areas {display:none;} .locationimage{display:none;} .locationdescription {display:none;} .fax{display:none;} </style>"; } ?>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="/core/scripts/formUpload.js"></script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="container pageBody location">
          <h1>Add <?php echo isset($_GET['useraddress']) ? "Address" : ucwords($row_rsLocationPrefs['locationdescriptor']); ?></h1>
        <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
        <p>
          <?php } ?>
        </p><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="add" id="add">
       
              
                <table  class="form-table">
                  <tr class="areas">
                    <td class="text-right">Category:</td>
                    <td><select name="categoryID"  id="categoryID" class="form-control">
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
                    </td>
                  </tr>
                  <tr>
                    <td class="text-right">Name: </td>
                    <td><span id="sprytextfield2">
                      <input name="locationname" type="text"  id="locationname" value="<?php echo isset($_GET['useraddress']) ? htmlentities($row_rsLoggedIn['fullname'], ENT_COMPAT, "UTF-8") : ""; ?>" size="40" maxlength="50"  class="form-control"/>
                    <br />
                    <span class="textfieldRequiredMsg">A name for this address is required.</span></span></td>
                  </tr>
                  <tr class="locationdescription">
                    <td class="text-right">Description: </td>
                    <td><textarea name="locationdescription" cols="40" rows="4" id="locationdescription"  class="form-control"><?php echo isset($_POST['locationdescription']) ? htmlentities($_POST['locationdescription'], ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
                  </tr>
                  <tr>
                    <td class="text-right">Address:</td>
                    <td><span id="sprytextfield1">
                      <input name="address1" type="text"  id="address1"  size="40" maxlength="50" value="<?php echo isset($_POST['address1']) ? htmlentities($_POST['address1'], ENT_COMPAT, "UTF-8") : ""; ?>"  />
<span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address2" type="text"  id="address2"  size="40" maxlength="50"  value="<?php echo isset($_POST['address2']) ? htmlentities($_POST['address2'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control" /></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address3" type="text"  id="address3" size="40" maxlength="50"  value="<?php echo isset($_POST['address3']) ? htmlentities($_POST['address3'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address4" type="text"  id="address4" size="40" maxlength="50"  value="<?php echo isset($_POST['address4']) ? htmlentities($_POST['address4'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">&nbsp;</td>
                    <td><input name="address5" type="text"  id="address5" size="40" maxlength="50"  value="<?php echo isset($_POST['address5']) ? htmlentities($_POST['address5'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">Postcode: </td>
                    <td><input name="postcode" type="text"  id="postcode" size="40" maxlength="10"  value="<?php echo isset($_POST['postcode']) ? htmlentities($_POST['postcode'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr class="country">
                    <td class="text-right">Country:</td>
                    <td><select name="countryID"  id="countryID" class="form-control">
                      <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsCountries['ID']?>"><?php echo $row_rsCountries['fullname']?></option>
                      <?php
} while ($row_rsCountries = mysql_fetch_assoc($rsCountries));
  $rows = mysql_num_rows($rsCountries);
  if($rows > 0) {
      mysql_data_seek($rsCountries, 0);
	  $row_rsCountries = mysql_fetch_assoc($rsCountries);
  }
?>
                    </select></td>
                  </tr>
                  <tr>
                    <td class="text-right">Telephone:</td>
                    <td><input name="telephone1" type="text"  id="telephone1" size="40" maxlength="20" value="<?php echo isset($_POST['telephone1']) ? htmlentities($_POST['telephone1'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr>
                    <td class="text-right">Alternative telephone:</td>
<td><input name="telephone2" type="text"  id="telephone2" size="40" maxlength="20"  value="<?php echo isset($_POST['telephone2']) ? htmlentities($_POST['telephone2'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control"/></td>
                  </tr>
                  <tr class="fax">
                    <td class="text-right">Fax:</td>
                    <td><input name="fax" type="text"  id="fax" size="40" maxlength="20" value="<?php echo isset($_POST['fax']) ? htmlentities($_POST['fax'], ENT_COMPAT, "UTF-8") : ""; ?>"  class="form-control"/></td>
                  </tr>
                  <tr class="locationimage upload">
                    <td class="text-nowrap text-right"><input type="hidden" name="MM_insert" value="add" />
                      <input type="hidden" name="directoryID" id="directoryID" value="<?php echo isset($_GET['directoryID']) ? htmlentities($_GET['directoryID'], ENT_COMPAT, "UTF-8") : ""; ?>" />
<input name="userID" type="hidden" id="userID" value="<?php // only if user address add userID
					  echo isset($_GET['useraddress']) ? $row_rsLoggedIn['ID'] : ""; ?>" />
                      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
                      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
                      <input name="referer" type="hidden" id="referer" value="<?php echo isset($_GET['redirectURL']) ? $_GET['redirectURL'] : $_SERVER['HTTP_REFERER']; ?>" />
                      <input name="imageURL" type="hidden" id="imageURL" />
                      Optional
                      Image:</td>
                    <td><input name="filename" type="file" class="fileinput" id="filename" size="20" /></td>
                  </tr>
                  <tr>
                    <td class="text-nowrap text-right">&nbsp;</td>
                    <td><div><button type="submit" class = "btn btn-primary" >Save</button></div></td>
                  </tr>
                </table>
        </form>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
//-->
</script></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsAreas);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsCountries);

mysql_free_result($rsLocationPrefs);
?>
