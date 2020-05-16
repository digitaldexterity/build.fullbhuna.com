<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
<?php

$regionID = isset($regionID) ? $regionID : 1;


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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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


 require_once(SITE_ROOT.'core/includes/upload.inc.php');
 
 	$uploaded = getUploads();
		
		  $_POST['shopfrontimageURL1'] = (isset($uploaded["filename"][1]["newname"]) && $uploaded["filename"][1]["newname"]!="") ? $uploaded["filename"][1]["newname"] : (isset($_POST['noImage'][1]) ? "" :  $_POST['shopfrontimageURL1']);
		 $_POST['shopfrontimageURL2'] = (isset($uploaded["filename"][2]["newname"]) && $uploaded["filename"][2]["newname"]!="") ? $uploaded["filename"][2]["newname"] : (isset($_POST['noImage'][2]) ? "" :  $_POST['shopfrontimageURL2']);
		 $_POST['shopfrontimageURL3'] = (isset($uploaded["filename"][3]["newname"]) && $uploaded["filename"][3]["newname"]!="") ? $uploaded["filename"][3]["newname"] : (isset($_POST['noImage'][3]) ? "" :  $_POST['shopfrontimageURL3']);
		 $_POST['shopfrontimageURL4'] = (isset($uploaded["filename"][4]["newname"]) && $uploaded["filename"][4]["newname"]!="") ? $uploaded["filename"][4]["newname"] : (isset($_POST['noImage'][4]) ? "" :  $_POST['shopfrontimageURL4']);
		 $_POST['shopfrontimageURL5'] = (isset($uploaded["filename"][5]["newname"]) && $uploaded["filename"][5]["newname"]!="") ? $uploaded["filename"][5]["newname"] : (isset($_POST['noImage'][5]) ? "" :  $_POST['shopfrontimageURL5']);
		 $_POST['shopfrontimageURL6'] = (isset($uploaded["filename"][6]["newname"]) && $uploaded["filename"][6]["newname"]!="") ? $uploaded["filename"][6]["newname"] : (isset($_POST['noImage'][6]) ? "" :  $_POST['shopfrontimageURL6']);
		 $_POST['shopfrontimageURL7'] = (isset($uploaded["filename"][7]["newname"]) && $uploaded["filename"][7]["newname"]!="") ? $uploaded["filename"][7]["newname"] : (isset($_POST['noImage'][7]) ? "" :  $_POST['shopfrontimageURL7']);
		 
		 
	
} // is post


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET shopfrontURL=%s, shopfrontfeatured=%s, shopfrontpopular=%s, shopfronttext=%s, shopfrontimagewidth=%s, shopfrontimageheight=%s, shopfrontimageURL1=%s, shopfrontimageURL2=%s, shopfrontimageURL3=%s, shopfrontimageURL4=%s, shopfrontimageURL5=%s, shopfrontimageURL6=%s, shopfrontimageURL7=%s, shopfrontlink1=%s, shopfrontlink2=%s, shopfrontlink3=%s, shopfrontlink4=%s, shopfrontlink5=%s, shopfrontlink6=%s, shopfrontlink7=%s, shopfrontimagealt1=%s, shopfrontimagealt2=%s, shopfrontimagealt3=%s, shopfrontimagealt4=%s, shopfrontimagealt5=%s, shopfrontimagealt6=%s, shopfrontimagealt7=%s, shopfront=%s WHERE ID=%s",
                       GetSQLValueString($_POST['shopfrontURL'], "text"),
                       GetSQLValueString($_POST['shopfrontfeatured'], "int"),
                       GetSQLValueString($_POST['shopfrontpopular'], "int"),
                       GetSQLValueString($_POST['shopfronttext'], "text"),
                       GetSQLValueString($_POST['shopfrontimagewidth'], "int"),
                       GetSQLValueString($_POST['shopfrontimageheight'], "int"),
                       GetSQLValueString($_POST['shopfrontimageURL1'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL2'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL3'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL4'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL5'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL6'], "text"),
                       GetSQLValueString($_POST['shopfrontimageURL7'], "text"),
                       GetSQLValueString($_POST['shopfrontlink1'], "text"),
                       GetSQLValueString($_POST['shopfrontlink2'], "text"),
                       GetSQLValueString($_POST['shopfrontlink3'], "text"),
                       GetSQLValueString($_POST['shopfrontlink4'], "text"),
                       GetSQLValueString($_POST['shopfrontlink5'], "text"),
                       GetSQLValueString($_POST['shopfrontlink6'], "text"),
                       GetSQLValueString($_POST['shopfrontlink7'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt1'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt2'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt3'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt4'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt5'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt6'], "text"),
                       GetSQLValueString($_POST['shopfrontimagealt7'], "text"),
                       GetSQLValueString(isset($_POST['shopfront']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$regionID = isset($regionID) ? $regionID : 1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Shop Front"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style><?php require_once('../../../../core/tinymce/tinymce.inc.php'); ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Shop Front</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
<li><a href="index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Options</a></li>

   

</ul></div></nav> 
<form action="<?php echo $editFormAction; ?>"  method="POST" enctype="multipart/form-data" name="form1" id="form1">
      <table class="form-table">
            <tr>
              <td align="right">Use shop front:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsProductPrefs['shopfront'],1))) {echo "checked=\"checked\"";} ?> name="shopfront" type="checkbox" id="shopfront" value="1" />
                Go to shop front instead of top category</label></td>
            </tr>
            <tr>
              <td align="right">Shop front URL:</td>
              <td><span id="sprytextfield1">
                <input name="shopfrontURL" type="text"  id="shopfrontURL" value="<?php echo $row_rsProductPrefs['shopfrontURL']; ?>" size="50" maxlength="255" placeholder="(optional bespoke shop front)" class="form-control" />
              </span></td>
            </tr>
            <tr>
              <td align="right">Main image width:</td>
              <td class="form-inline"><label for="shopfrontimagewidth"></label>
                <input name="shopfrontimagewidth" type="text"  id="shopfrontimagewidth" value="<?php echo $row_rsProductPrefs['shopfrontimagewidth']; ?>" size="4" maxlength="3" class="form-control"  />
                height:
                <label for="shopfrontimageheight"></label>
                <input name="shopfrontimageheight" type="text"  id="shopfrontimageheight" value="<?php echo $row_rsProductPrefs['shopfrontimageheight']; ?>" size="4" maxlength="3" class="form-control"  /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL1'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL1'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[1]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[1]" id="filename[1]" />
                <input name="shopfrontimageURL1" type="hidden" id="shopfrontimageURL1" value="<?php echo $row_rsProductPrefs['shopfrontimageURL1']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink1" type="text"  id="shopfrontlink1" value="<?php echo $row_rsProductPrefs['shopfrontlink1']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt1" type="text"  id="shopfrontimagealt1" value="<?php echo $row_rsProductPrefs['shopfrontimagealt1']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            
            
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL2'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL2'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[2]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[2]" id="filename[2]" />
                <input name="shopfrontimageURL2" type="hidden" id="shopfrontimageURL2" value="<?php echo $row_rsProductPrefs['shopfrontimageURL2']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink2" type="text"  id="shopfrontlink2" value="<?php echo $row_rsProductPrefs['shopfrontlink2']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt2" type="text"  id="shopfrontimagealt2" value="<?php echo $row_rsProductPrefs['shopfrontimagealt2']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            
            
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL3'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL3'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[3]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[3]" id="filename[3]" />
                <input name="shopfrontimageURL3" type="hidden" id="shopfrontimageURL3" value="<?php echo $row_rsProductPrefs['shopfrontimageURL3']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink3" type="text"  id="shopfrontlink3" value="<?php echo $row_rsProductPrefs['shopfrontlink3']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt3" type="text"  id="shopfrontimagealt3" value="<?php echo $row_rsProductPrefs['shopfrontimagealt3']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL4'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL4'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[4]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[4]" id="filename[4]" />
                <input name="shopfrontimageURL4" type="hidden" id="shopfrontimageURL4" value="<?php echo $row_rsProductPrefs['shopfrontimageURL4']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink4" type="text"  id="shopfrontlink4" value="<?php echo $row_rsProductPrefs['shopfrontlink4']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt4" type="text"  id="shopfrontimagealt4" value="<?php echo $row_rsProductPrefs['shopfrontimagealt4']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL5'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL5'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[5]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[5]" id="filename[5]" />
                <input name="shopfrontimageURL5" type="hidden" id="shopfrontimageURL5" value="<?php echo $row_rsProductPrefs['shopfrontimageURL5']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink5" type="text"  id="shopfrontlink5" value="<?php echo $row_rsProductPrefs['shopfrontlink5']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt5" type="text"  id="shopfrontimagealt5" value="<?php echo $row_rsProductPrefs['shopfrontimagealt5']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL6'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL6'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[6]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[6]" id="filename[6]" />
                <input name="shopfrontimageURL6" type="hidden" id="shopfrontimageURL6" value="<?php echo $row_rsProductPrefs['shopfrontimageURL6']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink6" type="text"  id="shopfrontlink6" value="<?php echo $row_rsProductPrefs['shopfrontlink6']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt6" type="text"  id="shopfrontimagealt6" value="<?php echo $row_rsProductPrefs['shopfrontimagealt6']; ?>" size="50" maxlength="100" class="form-control"  /></td>
            </tr>
            
            <tr>
              <td align="right" valign="top">Slideshow image:<br /></td>
              <td><?php if (isset($row_rsProductPrefs['shopfrontimageURL7'])) { ?>
                <img src="<?php echo getImageURL($row_rsProductPrefs['shopfrontimageURL7'], "thumb") ; ?>" alt="Default image" class="thumb" /><br />
                <input name="noImage[7]" type="checkbox" value="1" />
                Remove image
                <?php } else { ?>
                No  image set.
                <?php } ?>
                <br />
                Add/change image below:<br />
                <input type="file" name="filename[7]" id="filename[7]" />
                <input name="shopfrontimageURL7" type="hidden" id="shopfrontimageURL7" value="<?php echo $row_rsProductPrefs['shopfrontimageURL7']; ?>" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Link:</td>
              <td><input name="shopfrontlink7" type="text"  id="shopfrontlink7" value="<?php echo $row_rsProductPrefs['shopfrontlink7']; ?>" size="50" maxlength="100" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Alt tag:</td>
              <td><input name="shopfrontimagealt7" type="text"  id="shopfrontimagealt7" value="<?php echo $row_rsProductPrefs['shopfrontimagealt7']; ?>" size="50" maxlength="100" class="form-control"  /></td>
            </tr>
            
            <tr>
              <td align="right" valign="top">Featured products</td>
              <td class="form-inline"><label for="shopfrontfeatured"></label>
                <input name="shopfrontfeatured" type="text"  id="shopfrontfeatured" value="<?php echo $row_rsProductPrefs['shopfrontfeatured']; ?>" size="4" maxlength="3" class="form-control" />
                Popular products:
                <label for="shopfrontpopular"></label>
                <input name="shopfrontpopular" type="text"  id="shopfrontpopular" value="<?php echo $row_rsProductPrefs['shopfrontpopular']; ?>" size="4" maxlength="3" class="form-control" /></td>
            </tr>
            <tr>
              <td align="right" valign="top">Shop front text:</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2" valign="top"><label for="shopfronttext"></label>
                <textarea name="shopfronttext" id="shopfronttext" cols="45" rows="5" class="tinymce form-control" ><?php echo $row_rsProductPrefs['shopfronttext']; ?></textarea></td>
            </tr>
            <tr>
              <td colspan="2" valign="top"><button type="submit" class="btn btn-primary">Save changes</button>
      <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" /></td>
            </tr>
          </table>
      <input type="hidden" name="MM_update" value="form1">
      </form>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);
?>
