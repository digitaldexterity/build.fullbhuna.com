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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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
mysql_select_db($database_aquiescedb, $aquiescedb);
if(!mysql_fetch_assoc(mysql_query("SELECT * FROM mediaprefs WHERE ID = ".intval($regionID), $aquiescedb))) { // prefs don't exist yet so add
mysql_query("INSERT INTO mediaprefs (ID) VALUES (".intval($regionID).")", $aquiescedb) or die(mysql_error());
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
  $updateSQL = sprintf("UPDATE mediaprefs SET galleryhome=%s, showcomments=%s, showslideshow=%s, uselightbox=%s, allowlinks=%s, uploadpermissioncheck=%s, uploadrankID=%s, uploadapprove=%s, galleriesname=%s, imagesize_gallery=%s, gallerytype=%s WHERE ID=%s",
                       GetSQLValueString($_POST['galleryhome'], "int"),
                       GetSQLValueString(isset($_POST['showcomments']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showslideshow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['uselightbox']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowlinks']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['uploadpermissioncheck']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['uploadrankID'], "int"),
                       GetSQLValueString(isset($_POST['uploadapprove']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['galleriesname'], "text"),
                       GetSQLValueString($_POST['imagesize_gallery'], "text"),
                       GetSQLValueString($_POST['gallerytype'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);
 
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Photo Gallery Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationRadio.js"></script>
<link href="../../../SpryAssets/SpryValidationRadio.css" rel="stylesheet"  />
<link href="../../css/defaultGallery.css" rel="stylesheet"  />
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
  
  <h1><i class="glyphicon glyphicon-picture"></i>  Photo Gallery Options</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  
   <li class="nav-item"><a href="/photos/admin/" class="nav-link" rel="noopener"  ><i class="glyphicon glyphicon-arrow-left"></i> Back to Manage Photos</a></li>
  </ul></div></nav>
  <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
    On photo home page, show: <span id="spryradio1">
    <label>
      <input <?php if (!(strcmp($row_rsMediaPrefs['galleryhome'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="galleryhome" value="0" id="galleryHome_0" />
      latest photos</label>
    
    <label>
      <input <?php if (!(strcmp($row_rsMediaPrefs['galleryhome'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="galleryhome" value="1" id="galleryHome_1" />
      
galleries</label>
</span>
    <p>
      <label>
        <input name="showcomments" type="checkbox" id="showcomments" value="1" <?php if (!(strcmp($row_rsMediaPrefs['showcomments'],1))) {echo "checked=\"checked\"";} ?> />
        Show comments</label>
    </p>
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsMediaPrefs['showslideshow'],1))) {echo "checked=\"checked\"";} ?> name="showslideshow" type="checkbox" id="showslideshow" value="1" />
        Show slideshow</label>
    </p>
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsMediaPrefs['uselightbox'],1))) {echo "checked=\"checked\"";} ?> name="uselightbox" type="checkbox" id="uselightbox" value="1" />
        Use lightbox</label>
    </p>
    
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsMediaPrefs['allowlinks'],1))) {echo "checked=\"checked\"";} ?> name="allowlinks" type="checkbox" id="allowlinks" value="1" />
        Allow links</label>
    </p>
    <p class="form-inline">
      <label>Default upload rank: 
        <select name="uploadrankID" id="uploadrankID" class="form-control">
      <?php
do {  
?>
      <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], $row_rsMediaPrefs['uploadrankID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
      <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
    </select></label>
    </p>
    
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsMediaPrefs['uploadpermissioncheck'],1))) {echo "checked=\"checked\"";} ?> name="uploadpermissioncheck" type="checkbox" id="uploadpermissioncheck" value="1" />
        Ask if user has permission before upload</label>
    </p>
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsMediaPrefs['uploadapprove'],1))) {echo "checked=\"checked\"";} ?> name="uploadapprove" type="checkbox" id="uploadapprove" value="1" />
        Approve uploads before appearing on site</label>
    </p>
    <p>(round image corners on upload - set to 0 for no rounded corners)</p>
    <p>Gallery type: 
    <label><input <?php if (!(strcmp($row_rsMediaPrefs['gallerytype'],"0"))) {echo "checked=\"checked\"";} ?>  name="gallerytype" type="radio" value="0">&nbsp;None</label> &nbsp;&nbsp;&nbsp;
    <label><input <?php if (!(strcmp($row_rsMediaPrefs['gallerytype'],"1"))) {echo "checked=\"checked\"";} ?> name="gallerytype" type="radio" value="1">&nbsp;Square grid</label> &nbsp;&nbsp;&nbsp;
    <label><input <?php if (!(strcmp($row_rsMediaPrefs['gallerytype'],"2"))) {echo "checked=\"checked\"";} ?> name="gallerytype" type="radio" value="2">&nbsp;Masonry</label> &nbsp;&nbsp;&nbsp;</p>
   
    <p class="form=inline">Gallery thumb size: 
      <select name="imagesize_gallery" id="imagesize_gallery" class="form-control">
        <option value="" <?php if (!(strcmp("", $row_rsMediaPrefs['imagesize_gallery']))) {echo "selected=\"selected\"";} ?>>Choose image size...</option>
        <?php foreach($image_sizes as $size=>$values) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
        <option value="<?php echo $size; ?>" <?php if (!(strcmp($size, $row_rsMediaPrefs['imagesize_gallery']))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].")","x "); ?></option>
        <?php } ?>
        <option value="" <?php if (!(strcmp("", $row_rsMediaPrefs['imagesize_gallery']))) {echo "selected=\"selected\"";} ?>>Full size</option>
      </select>
    </p>
    <p class="form-inline"><label>Galleries name: 
      
        <input name="galleriesname" type="text" id="galleriesname" value="<?php echo $row_rsMediaPrefs['galleriesname']; ?>" size="50" maxlength="50" class="form-control">
      </label>
    </p>
    <p>
     
        <button name="save" type="submit" class="btn btn-primary" id="save" >Save changes</button>
        
        
     
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsMediaPrefs['ID']; ?>" />
    </p>
    <input type="hidden" name="MM_update" value="form1" />
  </form>
  
  <script>
<!--
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1", {isRequired:false});
//-->
  </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsMediaPrefs);

mysql_free_result($rsUserTypes);
?>
