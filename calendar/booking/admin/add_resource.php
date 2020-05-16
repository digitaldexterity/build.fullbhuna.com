<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO bookingresource (title, `description`,  locationID, categoryID,  notifyemail,  imageURL, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                      
                       GetSQLValueString($_POST['locationID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                      
                       GetSQLValueString($_POST['notifyemail'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "update_resource.php?tab=2&resourceID=".mysql_insert_id();
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = "SELECT ID, locationname FROM location WHERE active = 1 AND location.categoryID >=1 ORDER BY locationname ASC";
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype WHERE ID > 1";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

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
$query_rsBookingCategories = "SELECT ID, `description` FROM bookingcategory WHERE statusID = 1 ORDER BY `description` ASC";
$rsBookingCategories = mysql_query($query_rsBookingCategories, $aquiescedb) or die(mysql_error());
$row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories);
$totalRows_rsBookingCategories = mysql_num_rows($rsBookingCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT contactemail FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Booking Resource"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar"><script>
    function validateForm(form)
{
form.availabledow.value = (form.mon.checked == true ? "1" : "")+(form.tue.checked == true ? "2" : "")+(form.wed.checked == true ? "3" : "")+(form.thu.checked == true ? "4" : "")+(form.fri.checked == true ? "5" : "")+(form.sat.checked == true ? "6" : "")+(form.sun.checked == true ? "7" : "");
var errors = '';
if (form.availabledow.value == "") errors +="Please enter at least one day\n";
if (form.availablestart.value == "") errors +="Please enter a start time\n";
if (form.availableend.value == "") errors +="Please enter an end time\n";

if (form.title.value == "") errors +="Please enter a resource name\n";


 if (errors) window.alert(errors); 
  
   document.returnValue = (!errors);
 }
              </script>
      <h1>Add Booking Resource</h1>
      <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  onsubmit="validateForm(document.form1); return document.returnValue;" role="form" >
        <table class="form-table"> <tr>
            <td class="nowrap text-right">Category:</td>
            <td><select name="categoryID" id="categoryID">
              <option value="" <?php if (!(strcmp(0, $row_rsBookingCategories['ID']))) {echo "selected=\"selected\"";} ?>>None</option>
              <?php
do {  
?><option value="<?php echo $row_rsBookingCategories['ID']?>"<?php if (!(strcmp($row_rsBookingCategories['ID'], $row_rsBookingCategories['ID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsBookingCategories['description']?></option>
              <?php
} while ($row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories));
  $rows = mysql_num_rows($rsBookingCategories);
  if($rows > 0) {
      mysql_data_seek($rsBookingCategories, 0);
	  $row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories);
  }
?>
            </select> 
              <a href="categories/index.php">Add category</a></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Location:</td>
            <td><label>
              <select name="locationID" id="locationID">
                <option value="" <?php if (!(strcmp(0, $row_rsLocations['ID']))) {echo "selected=\"selected\"";} ?>>Not applicable</option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsLocations['ID']?>"<?php if (!(strcmp($row_rsLocations['ID'], $row_rsLocations['ID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocations['locationname']?></option>
                <?php
} while ($row_rsLocations = mysql_fetch_assoc($rsLocations));
  $rows = mysql_num_rows($rsLocations);
  if($rows > 0) {
      mysql_data_seek($rsLocations, 0);
	  $row_rsLocations = mysql_fetch_assoc($rsLocations);
  }
?>
              </select>
            <a href="../../../location/admin/index.php">Add location</a></label></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Title:</td>
            <td><input type="text"  name="title" value="" size="32" /></td>
          </tr> <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td><textarea name="description" cols="50" rows="5"></textarea>            </td>
          </tr><?php if (!(strcmp($row_rsResource['interval'],"0"))) { ?><script>document.getElementById('availabletime').style.display = "none";</script>
 <?php } ?> <tr>
            <td height="21" class="text-nowrap text-right">Notification to email:</td>
            <td><input name="notifyemail" type="text"  id="notifyemail" value="<?php echo $row_rsPreferences['contactemail']; ?>" size="40" maxlength="100" /></td>
          </tr>
          <tr class="upload">
            <td class="text-nowrap text-right">Optional image:</td>
            <td><input name="filename" type="file" class="fileinput" id="filename" size="20" maxlength="255" />
            <input type="hidden" name="imageURL" id="imageURL" /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><input type="submit" class="button" value="Add Resource" /></td>
          </tr>
        </table>
        <input type="hidden" name="availabledow" value="" />
        <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input type="hidden" name="statusID" value="1" />
        <input type="hidden" name="MM_insert" value="form1" />
      </form></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocations);

mysql_free_result($rsUserType);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsBookingCategories);

mysql_free_result($rsPreferences);
?>
