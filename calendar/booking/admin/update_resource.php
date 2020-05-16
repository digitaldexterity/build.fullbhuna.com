<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

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

$orderBy = (isset($_GET['orderby']) && $_GET['orderby'] == "startdatetime") ? "startdatetime" : "createddatetime";

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { // update features
mysql_select_db($database_aquiescedb, $aquiescedb);
$delete = sprintf("DELETE FROM bookingresourcefeature WHERE resourceID = %s",GetSQLValueString($_POST['ID'], "int")); // delete all current records before insert new ones
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
if (isset($_POST['feature'])) { // if features sent
foreach($_POST['feature'] as $featureID => $value) {
if ($value == 1) { // if checked
$insert = sprintf("INSERT INTO bookingresourcefeature (featureID, resourceID) VALUES (%s, %s)",GetSQLValueString($featureID, "int"),GetSQLValueString($_POST['ID'], "int")); // delete all current records before insert new ones
$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
} // end is checked
} // end foreach
} // end features sent
} // end update features

if (isset($_POST['startdatetime'])) { $startDate = $_POST['startdatetime']; } else if (isset($_GET['startDate'])) { $startDate = $_GET['startDate']." 09:00:00"; } else { $startDate = date('Y-m-d H:i:s'); } // set date 

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE bookingresource SET title=%s, `description`=%s, availablestart=%s, availableend=%s, availabledow=%s, availabletoID=%s, locationID=%s, categoryID=%s, maxHours=%s, minNotice=%s, recurringallow=%s, capacitystanding=%s, capacitytheatre=%s, capacityclassroom=%s, capacityboardroom=%s, capacitybanquet=%s, `interval`=%s, notifyemail=%s, paymentrequired=%s, paymentnotes=%s, imageURL=%s, featurenotes=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['availablestart'], "date"),
                       GetSQLValueString($_POST['availableend'], "date"),
                       GetSQLValueString($_POST['availabledow'], "text"),
                       GetSQLValueString($_POST['availabletoID'], "int"),
                       GetSQLValueString($_POST['locationID'], "int"),
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['maxHours'], "int"),
                       GetSQLValueString($_POST['minNotice'], "int"),
                       GetSQLValueString(isset($_POST['recurringallow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['capacitystanding'], "int"),
                       GetSQLValueString($_POST['capacitytheatre'], "int"),
                       GetSQLValueString($_POST['capacityclassroom'], "int"),
                       GetSQLValueString($_POST['capacityboardroom'], "int"),
                       GetSQLValueString($_POST['capacitybanquet'], "int"),
                       GetSQLValueString($_POST['interval'], "int"),
                       GetSQLValueString($_POST['notifyemail'], "text"),
                       GetSQLValueString(isset($_POST['paymentRequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['paymentnotes'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['featurenotes'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifiedddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "" . $_POST['redirect'] . "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = "SELECT ID, locationname FROM location WHERE active = 1 ORDER BY locationname ASC";
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype WHERE ID > 1";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsResource = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsResource = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResource = sprintf("SELECT * FROM bookingresource WHERE ID = %s", GetSQLValueString($colname_rsResource, "int"));
$rsResource = mysql_query($query_rsResource, $aquiescedb) or die(mysql_error());
$row_rsResource = mysql_fetch_assoc($rsResource);
$totalRows_rsResource = mysql_num_rows($rsResource);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBookingCategories = "SELECT ID, `description` FROM bookingcategory WHERE statusID = 1 ORDER BY `description` ASC";
$rsBookingCategories = mysql_query($query_rsBookingCategories, $aquiescedb) or die(mysql_error());
$row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories);
$totalRows_rsBookingCategories = mysql_num_rows($rsBookingCategories);

$varCategoryID_rsFeatures = "0";
if (isset($row_rsResource['categoryID'])) {
  $varCategoryID_rsFeatures = $row_rsResource['categoryID'];
}
$varResourceID_rsFeatures = "-1";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsFeatures = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFeatures = sprintf("SELECT bookingfeature.ID, bookingfeature.featurename, bookingresourcefeature.ID AS checked FROM bookingfeature LEFT JOIN bookingresourcefeature ON (bookingfeature.ID = bookingresourcefeature.featureID AND bookingresourcefeature.resourceID = %s) WHERE bookingfeature.categoryID = %s OR bookingfeature.categoryID = 0 ORDER BY bookingfeature.featurename", GetSQLValueString($varResourceID_rsFeatures, "int"),GetSQLValueString($varCategoryID_rsFeatures, "int"));
$rsFeatures = mysql_query($query_rsFeatures, $aquiescedb) or die(mysql_error());
$row_rsFeatures = mysql_fetch_assoc($rsFeatures);
$totalRows_rsFeatures = mysql_num_rows($rsFeatures);

$colname_rsPaymentRates = "-1";
if (isset($_GET['resourceID'])) {
  $colname_rsPaymentRates = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPaymentRates = sprintf("SELECT * FROM bookingpricing WHERE resourceID = %s ORDER BY `default` DESC, bookingpricing.datestart", GetSQLValueString($colname_rsPaymentRates, "int"));
$rsPaymentRates = mysql_query($query_rsPaymentRates, $aquiescedb) or die(mysql_error());
$row_rsPaymentRates = mysql_fetch_assoc($rsPaymentRates);
$totalRows_rsPaymentRates = mysql_num_rows($rsPaymentRates);

$maxRows_rsBooked = 50;
$pageNum_rsBooked = 0;
if (isset($_GET['pageNum_rsBooked'])) {
  $pageNum_rsBooked = $_GET['pageNum_rsBooked'];
}
$startRow_rsBooked = $pageNum_rsBooked * $maxRows_rsBooked;

$varResourceID_rsBooked = "7";
if (isset($_GET['resourceID'])) {
  $varResourceID_rsBooked = $_GET['resourceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBooked = sprintf("SELECT bookinginstance.bookedfor, bookinginstance.confirmed, bookinginstance.startdatetime, bookinginstance.enddatetime, bookinginstance.ID, bookinginstance.statusID FROM bookinginstance WHERE bookinginstance.resourceID = %s ORDER BY ".$orderBy." DESC", GetSQLValueString($varResourceID_rsBooked, "int"));
$query_limit_rsBooked = sprintf("%s LIMIT %d, %d", $query_rsBooked, $startRow_rsBooked, $maxRows_rsBooked);
$rsBooked = mysql_query($query_limit_rsBooked, $aquiescedb) or die(mysql_error());
$row_rsBooked = mysql_fetch_assoc($rsBooked);

if (isset($_GET['totalRows_rsBooked'])) {
  $totalRows_rsBooked = $_GET['totalRows_rsBooked'];
} else {
  $all_rsBooked = mysql_query($query_rsBooked);
  $totalRows_rsBooked = mysql_num_rows($all_rsBooked);
}
$totalPages_rsBooked = ceil($totalRows_rsBooked/$maxRows_rsBooked)-1;

$queryString_rsBooked = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsBooked") == false && 
        stristr($param, "totalRows_rsBooked") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsBooked = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsBooked = sprintf("&totalRows_rsBooked=%d%s", $totalRows_rsBooked, $queryString_rsBooked);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Booking Resource"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
      <script>
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
      <h1>Update <?php echo $row_rsResource['title']; ?></h1>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  onsubmit="validateForm(document.form1); return document.returnValue;" role="form" >
  <div id="TabbedPanels1" class="TabbedPanels">
        <ul class="TabbedPanelsTabGroup">
          <li class="TabbedPanelsTab" tabindex="0">Bookings</li>
          <li class="TabbedPanelsTab" tabindex="0">Details</li>
          <li class="TabbedPanelsTab" tabindex="0">Availability</li>
          <li class="TabbedPanelsTab" tabindex="0">Capacity</li>
          <li class="TabbedPanelsTab" tabindex="0">Features</li>
          <li class="TabbedPanelsTab" tabindex="0">Payment</li>
          </ul>
        <div class="TabbedPanelsContentGroup">
          <div class="TabbedPanelsContent">
         
            
            <?php if ($totalRows_rsBooked == 0) { // Show if recordset empty ?>
            <p>This resource has not been booked.</p>
            <?php } // Show if recordset empty ?>
            <?php if ($totalRows_rsBooked > 0) { // Show if recordset not empty ?>
<p>Bookings <?php echo ($startRow_rsBooked + 1) ?> to <?php echo min($startRow_rsBooked + $maxRows_rsBooked, $totalRows_rsBooked) ?> of <?php echo $totalRows_rsBooked ?> ordered by  
  <select name="orderby" id="orderby" onChange="window.location.href='<?php echo $_SERVER['PHP_SELF']."?resourceID=".intval($_GET['resourceID'])."&orderby="; ?>'+this.value">
    <option value="createddatetime">Date booking made</option>
    <option value="startdatetime" <?php if ($_GET['orderby'] == "startdatetime") echo "selected='selected'"; ?>>Date booking for</option>
  </select> descending.
</p>
<table border="0" cellpadding="0" cellspacing="0" class="listTable">
              <tr>
                <td>&nbsp;</td>
                <td><strong>Booked for:</strong></td>
                <td><strong>From:</strong></td>
                <td>&nbsp;</td>
                <td><strong>Until:</strong></td>
                <td><strong>Edit</strong></td>
              </tr>
              <?php do { ?>
              <tr>
                <td id="status<?php echo $row_rsBooked['ID']; ?>"><a href="javascript:void(0);" onClick="getData('updatebookingstatus.php?bookingID=<?php echo $row_rsBooked['ID']; ?>&statusID=<?php echo $row_rsBooked['statusID']; ?>','status<?php echo $row_rsBooked['ID']; ?>')" title="Click here to change booking status"><?php if (($row_rsBooked['statusID'] == 1)) { ?>
                    <img src="../../../core/images/icons/green-light.png" alt="This booking is confirmed" style="vertical-align:
middle;" />
                  <?php } else if (($row_rsBooked['statusID'] == 0)) { ?>
                  <img src="../../../core/images/icons/amber-light.png" alt="This booking has not been confirmed" width="16" height="16" style="vertical-align:
middle;" />
                  <?php } else { ?>
                  <img src="../../../core/images/icons/red-light.png" alt="This booking has been refused or cancelled" width="16" height="16" style="vertical-align:
middle;" />
                    <?php } ?></a></td>
                <td><?php echo $row_rsBooked['bookedfor']; ?></td>
                <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['startdatetime'])); ?></td>
                <td>&raquo;&raquo;</td>
                <td><?php echo date('D d M Y g.i a',strtotime($row_rsBooked['enddatetime'])); ?></td>
                <td><a href="update_booking.php?resourceID=<?php echo $row_rsResource['ID']; ?>&amp;bookingID=<?php echo $row_rsBooked['ID']; ?>" class="link_edit icon_only">Edit</a></td>
              </tr>
              <?php } while ($row_rsBooked = mysql_fetch_assoc($rsBooked)); ?>
            </table>
           
              <?php } // Show if recordset not empty ?>
           
          
          <table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsBooked > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsBooked=%d%s", $currentPage, 0, $queryString_rsBooked); ?>">First</a>
                    <?php } // Show if not first page ?>
              </td>
              <td><?php if ($pageNum_rsBooked > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_rsBooked=%d%s", $currentPage, max(0, $pageNum_rsBooked - 1), $queryString_rsBooked); ?>" rel="prev">Previous</a>
                    <?php } // Show if not first page ?>
              </td>
              <td><?php if ($pageNum_rsBooked < $totalPages_rsBooked) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsBooked=%d%s", $currentPage, min($totalPages_rsBooked, $pageNum_rsBooked + 1), $queryString_rsBooked); ?>" rel="next">Next</a>
                    <?php } // Show if not last page ?>
              </td>
              <td><?php if ($pageNum_rsBooked < $totalPages_rsBooked) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_rsBooked=%d%s", $currentPage, $totalPages_rsBooked, $queryString_rsBooked); ?>">Last</a>
                    <?php } // Show if not last page ?>
              </td>
            </tr>
          </table>
          
</div>
          <div class="TabbedPanelsContent">
            <table class="form-table"> <tr>
                <td class="text-nowrap text-right">Category:</td>
                <td><select name="categoryID" id="categoryID">
                    <option value="0" <?php if (!(strcmp(0, $row_rsResource['categoryID']))) {echo "selected=\"selected\"";} ?>>None</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsBookingCategories['ID']?>"<?php if (!(strcmp($row_rsBookingCategories['ID'], $row_rsResource['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsBookingCategories['description']?></option>
                    <?php
} while ($row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories));
  $rows = mysql_num_rows($rsBookingCategories);
  if($rows > 0) {
      mysql_data_seek($rsBookingCategories, 0);
	  $row_rsBookingCategories = mysql_fetch_assoc($rsBookingCategories);
  }
?>
                  </select>
                  <a href="categories/index.php">Manage categories</a></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Location:</td>
                <td><label>
                  <select name="locationID" id="locationID">
                    <option value="0" <?php if (!(strcmp(0, $row_rsResource['locationID']))) {echo "selected=\"selected\"";} ?>>Not applicable</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsLocations['ID']?>"<?php if (!(strcmp($row_rsLocations['ID'], $row_rsResource['locationID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsLocations['locationname']?></option>
                    <?php
} while ($row_rsLocations = mysql_fetch_assoc($rsLocations));
  $rows = mysql_num_rows($rsLocations);
  if($rows > 0) {
      mysql_data_seek($rsLocations, 0);
	  $row_rsLocations = mysql_fetch_assoc($rsLocations);
  }
?>
                  </select>
                <a href="../../../location/admin/index.php">Manage locations</a></label></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Title:</td>
                <td><input type="text"  name="title" value="<?php echo $row_rsResource['title']; ?>" size="32" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right top">Description:</td>
                <td><textarea name="description" cols="50" rows="5"><?php echo $row_rsResource['description']; ?></textarea>                </td>
              </tr>
              <?php if (!(strcmp($row_rsResource['interval'],"0"))) { ?>
              <script>document.getElementById('availabletime').style.display = "none";</script>
              <?php } ?> <tr>
                <td height="21" class="text-nowrap text-right">Notification to:</td>
                <td><input name="notifyemail" type="text"  id="notifyemail" value="<?php echo $row_rsResource['notifyemail']; ?>" size="40" maxlength="100" /></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Optional image:</td>
                <td><?php if (isset($row_rsResource['imageURL'])) { ?>
                      <img src="<?php echo getImageURL($row_rsResource['imageURL'],"medium"); ?>" alt="" /><br />
                      <input name="noImage" type="checkbox" value="1" />
                    Remove image
                    <?php } else { ?>
                    No image associated with this resource.
                    <?php } ?>
                  <span class="upload"><br />
                  Add/change image below:<br />
                  <input name="filename" type="file" class="fileinput" id="filename" size="20" maxlength="255" />
                <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsResource['imageURL']; ?>" /></span></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Status:</td>
                <td><select name="statusID" id="statusID">
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsResource['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
                    <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
                </select></td>
              </tr>
            </table>
          </div>
          <div class="TabbedPanelsContent">
            <table class="form-table"> <tr>
                <td class="text-nowrap text-right">Available on:</td>
                <td><label for="mon">Mon:</label>
                    <input <?php if (stristr($row_rsResource['availabledow'],"1")) {echo "checked=\"checked\"";} ?> name="mon" type="checkbox" id="mon" />
                    <label for="tue">Tue:</label>
                    <input name="tue" type="checkbox" id="tue" <?php if (stristr($row_rsResource['availabledow'],"2")) {echo "checked=\"checked\"";} ?> />
                    <label for="wed">Wed:</label>
                    <input name="wed" type="checkbox" id="wed" <?php if (stristr($row_rsResource['availabledow'],"3")) {echo "checked=\"checked\"";} ?> />
                    <label for="thu">Thu:</label>
                    <input name="thu" type="checkbox" id="thu" <?php if (stristr($row_rsResource['availabledow'],"4")) {echo "checked=\"checked\"";} ?> />
                    <label for="fri">Fri:</label>
                    <input name="fri" type="checkbox" id="fri" <?php if (stristr($row_rsResource['availabledow'],"5")) {echo "checked=\"checked\"";} ?> />
                    <label for="sat">Sat:</label>
                    <input type="checkbox" name="sat" id="sat" <?php if (stristr($row_rsResource['availabledow'],"6")) {echo "checked=\"checked\"";} ?> />
                    <label for="sun">Sun:</label>
                    <input type="checkbox" name="sun" id="sun" <?php if (stristr($row_rsResource['availabledow'],"7")) {echo "checked=\"checked\"";} ?> />                </td>
              </tr> <tr>
                <td class="text-nowrap text-right">Available to:</td>
                <td><select name="availabletoID">
                    <option value="0"  <?php if (!(strcmp(0, $row_rsResource['availabletoID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $row_rsResource['availabletoID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['description']?></option>
                    <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
                  </select>
                    <label></label></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Maximum time:</td>
                <td><label>
                  <input <?php if (!(strcmp($row_rsResource['maxHours'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="maxHours" id="maxHours" value="1" />
                  1 hour
                  <input <?php if (!(strcmp($row_rsResource['maxHours'],"24"))) {echo "checked=\"checked\"";} ?> type="radio" name="maxHours" id="maxHours" value="24" />
                  1 day
                  <input <?php if (!(strcmp($row_rsResource['maxHours'],"168"))) {echo "checked=\"checked\"";} ?> type="radio" name="maxHours" id="maxHours" value="168" />
                  1 Week
                  <input <?php if (!(strcmp($row_rsResource['maxHours'],"744"))) {echo "checked=\"checked\"";} ?> type="radio" name="maxHours" id="maxHours" value="744" />
                  1 month
                  <input <?php if (!(strcmp($row_rsResource['maxHours'],"999999"))) {echo "checked=\"checked\"";} ?> name="maxHours" type="radio" id="maxHours" value="999999" />
                  Unlimited</label></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Minimum notice:</td>
                <td><label>
                  <input <?php if (!(strcmp($row_rsResource['minNotice'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="minNotice" id="minNotice" value="1" />
                  1 hour
                  <input <?php if (!(strcmp($row_rsResource['minNotice'],"24"))) {echo "checked=\"checked\"";} ?> type="radio" name="minNotice" id="minNotice" value="24" />
                  1 day
                  <input <?php if (!(strcmp($row_rsResource['minNotice'],"168"))) {echo "checked=\"checked\"";} ?> type="radio" name="minNotice" id="minNotice" value="168" />
                  1 Week
                  <input <?php if (!(strcmp($row_rsResource['minNotice'],"744"))) {echo "checked=\"checked\"";} ?> type="radio" name="minNotice" id="minNotice" value="744" />
                  1 month
                  <input <?php if (!(strcmp($row_rsResource['minNotice'],"0"))) {echo "checked=\"checked\"";} ?> name="minNotice" type="radio" id="minNotice" value="0" />
                  None</label></td>
              </tr> <tr>
                <td height="21" class="text-nowrap text-right">Booking interval:</td>
                <script>function showHide(isHour) { 
			if (isHour == "1") { document.getElementById('availabletime').style.display = ""; } else { document.getElementById('availabletime').style.display = "none"; return document.returnValue; }}</script>
                <td><input <?php if (!(strcmp($row_rsResource['interval'],"0"))) {echo "checked=\"checked\"";} ?> name="interval" type="radio" id="interval" value="0" onclick = "javascript:showHide('0');"/>
                  by day
                  <input <?php if (!(strcmp($row_rsResource['interval'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="interval" id="interval" value="1" onclick = "javascript:showHide('1');"/>
                  by the hour</td>
              </tr>
              <tr id="availabletime">
                <td class="text-nowrap text-right">Available from:</td>
                <td><input type="hidden" name="availablestart" value="<?php $setvalue = $row_rsResource['availablestart']; echo $setvalue; ?>" size="32" />
                    <?php $showdate = false;$time = true; $inputname = "availablestart"; include("../../../core/includes/datetimeinput.inc.php"); ?>
                  until:
                  <input type="hidden" name="availableend" value="<?php $setvalue = $row_rsResource['availableend']; echo $setvalue; ?>" size="32" />
                  <?php $showdate = false;$time = true; $inputname = "availableend"; include("../../../core/includes/datetimeinput.inc.php"); ?></td>
              </tr> <tr>
                <td class="text-nowrap text-right">Recursive Booking:</td>
                <td><input <?php if (!(strcmp($row_rsResource['recurringallow'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="recurringallow" id="recurringallow" />
                check to allow weekly recurring bookings</td>
              </tr>
</table>
          <?php if (!(strcmp($row_rsResource['interval'],"0"))) { ?>
              <script>document.getElementById('availabletime').style.display = "none";</script>
              <?php } ?>
          </div>
          <div class="TabbedPanelsContent">
            <p> Enter the capcity of the venue resource in various formations. Leave blank if not applicable.</p>
            <table border="0" cellpadding="2" cellspacing="0" class="form-table">
              <tr>
                <td><div align="right">Standing</div></td>
                <td><input name="capacitystanding" type="text"  id="capacitystanding" value="<?php echo $row_rsResource['capacitystanding']; ?>" size="5" maxlength="5" /></td>
              </tr>
              <tr>
                <td><div align="right">Theatre</div></td>
                <td><input name="capacitytheatre" type="text"  id="capacitytheatre" value="<?php echo $row_rsResource['capacitytheatre']; ?>" size="5" maxlength="5" /></td>
              </tr>
              <tr>
                <td><div align="right">Classroom</div></td>
                <td><input name="capacityclassroom" type="text"  id="capacityclassroom" value="<?php echo $row_rsResource['capacityclassroom']; ?>" size="5" maxlength="5" /></td>
              </tr>
              <tr>
                <td><div align="right">Boardroom</div></td>
                <td><input name="capacityboardroom" type="text"  id="capacityboardroom" value="<?php echo $row_rsResource['capacityboardroom']; ?>" size="5" maxlength="5" /></td>
              </tr>
              <tr>
                <td><div align="right">Banquet</div></td>
                <td><input name="capacitybanquet" type="text"  id="capacitybanquet" value="<?php echo $row_rsResource['capacitybanquet']; ?>" size="5" maxlength="5" /></td>
              </tr>
            </table>
          </div>
          <div class="TabbedPanelsContent">
            <p>You can optionally add and check available features for each bookable resource you add.            </p>
            <?php if ($totalRows_rsFeatures == 0) { // Show if recordset empty ?>
              <p>There are no features stored in the database</p>
              <?php } // Show if recordset empty ?>
<p><a href="javascript:document.getElementById('redirect').value='features/index.php';document.getElementById('form1').submit();" >Manage available features</a></p>
            <?php if ($totalRows_rsFeatures > 0) { // Show if recordset not empty ?>
              <?php do { ?>
    
    <p><label><input name="feature[<?php echo $row_rsFeatures['ID']; ?>]" type="checkbox" id="feature[<?php echo $row_rsFeatures['ID']; ?>]" value="1" <?php if(isset($row_rsFeatures['checked'])) { echo "checked=\"checked\""; } ?> />
    <?php echo $row_rsFeatures['featurename']; ?></label>
    </p>
    <?php } while ($row_rsFeatures = mysql_fetch_assoc($rsFeatures)); ?>
              <?php } // Show if recordset not empty ?>
<p><strong>Optional Notes:</strong></p>
<p>
  <textarea name="featurenotes" id="featurenotes" cols="60" rows="5"><?php echo $row_rsResource['featurenotes']; ?></textarea>
</p>
          </div>
          <div class="TabbedPanelsContent"><?php if (strcmp($row_rsResource['paymentrequired'],1)) {?><style>#paymentsettings {display:none;}</style><?php } ?>
            <p>
              <input <?php if (!(strcmp($row_rsResource['paymentrequired'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="paymentRequired" id="paymentRequired" onClick="if(this.checked) document.getElementById('paymentsettings').style.display = ''; else document.getElementById('paymentsettings').style.display = 'none';" />
Payment required to book this resource</p>
            <div id="paymentsettings">
            <h2>Payment details:</h2>
            <p>
              <textarea name="paymentnotes" id="paymentnotes" cols="45" rows="5"><?php echo $row_rsResource['paymentnotes']; ?></textarea>
            </p>
            <h2>Payment Rates</h2>
            <?php if ($totalRows_rsPaymentRates == 0) { // Show if recordset empty ?>
              <p>There are no payment rates entered.</p>
              <?php } // Show if recordset empty ?>
<p><a href="payment/add_payment.php?resourceID=<?php echo intval($_GET['resourceID']); ?>">Add payment rate</a></p>
            
          
            
            <?php if ($totalRows_rsPaymentRates > 0) { // Show if recordset not empty ?>
                <table border="0" cellpadding="0" cellspacing="0" class="listTable">
                  <tr>
                    <td><strong>Def</strong></td>
                    <td><strong>Rate</strong></td>
                  
                    <td><strong>Dates</strong></td>
                    <td><strong>Times</strong></td>
                    <td><strong>Dow</strong></td>
                    
                    <td colspan="2"><strong>Edit</strong></td>
                  </tr>
                  <?php do { ?><?php $hours = $row_rsPaymentRates['pricehours'];
		 if (($hours/168) == number_format(($hours/168),0)) { // is weeks
		 $periodamount = $hours/168; $periodmultiple = ($periodamount == 1) ? "week" : $periodamount." weeks";
		 } else if (($hours/24) == number_format(($hours/24),0)) { // is days
		 	 $periodamount = $hours/24; $periodmultiple = ($periodamount == 1) ? "day" : $periodamount." days";
			 } else { // is hours
			 $periodamount = $hours; $periodmultiple = ($periodamount == 1) ? "hour" : $periodamount." hours";
			 }
		 ?>
                    <tr>
                      <td class="top">&nbsp;<?php if ($row_rsPaymentRates['default']==1) { ?><span class="glyphicon glyphicon-ok"></span><?php } ?></td>
                      <td class="top"><?php echo number_format($row_rsPaymentRates['price'],2).$row_rsPaymentRates['currency']." per ".$periodmultiple."<br />".number_format($row_rsPaymentRates['deposit']).$row_rsPaymentRates['currency']." deposit"; ?></td>
                      
                      <td class="top"><?php echo $row_rsPaymentRates['datestart']; ?><br /><?php echo $row_rsPaymentRates['dateend']; ?></td>
                      <td class="top"><?php echo $row_rsPaymentRates['timestart']; ?><br /><?php echo $row_rsPaymentRates['timeend']; ?></td>
                     
                      <td class="top"><?php echo $row_rsPaymentRates['daysofweek']; ?></td>
                      
                      <td class="top"><a href="payment/update_payment.php?paymentID=<?php echo $row_rsPaymentRates['ID']; ?>&amp;resourceID=<?php echo intval($_GET['resourceID']); ?>">Edit</a></td>
                      <td class="top">Delete</td>
                    </tr>
                    <?php } while ($row_rsPaymentRates = mysql_fetch_assoc($rsPaymentRates)); ?>
              </table>
            <?php } // Show if recordset not empty ?></div><!--div payment settings-->
</div>
        </div>
  </div>
      
<input name="availabledow" type="hidden" value="<?php echo $row_rsResource['availabledow']; ?>" />
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input name="modifiedddatetime" type="hidden" id="modifiedddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        
  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsResource['ID']; ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input name="redirect" type="hidden" id="redirect" value="index.php" />
      <button type="submit" class="btn btn-primary" >Save changes</button>
    </form>
    <?php if (isset($_GET['tab'])) { echo '<script type="text/javascript">
<!--
var client = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['tab']).'});
//-->
    </script>'; } else { ?>
         
    <script>
<!--
var client = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
    </script>
<?php } ?></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLocations);

mysql_free_result($rsUserType);

mysql_free_result($rsStatus);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsResource);

mysql_free_result($rsBookingCategories);

mysql_free_result($rsFeatures);

mysql_free_result($rsPaymentRates);

mysql_free_result($rsBooked);
?>
