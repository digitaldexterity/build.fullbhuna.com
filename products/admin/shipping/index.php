<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

if(isset($_GET['deleteshippingrateID'])) {
	$delete = "DELETE FROM productshipping WHERE ID = ".intval($_GET['deleteshippingrateID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	mysql_query($delete, $aquiescedb) or die(mysql_error());
}

if(isset($_GET['copyshippingregionID']) && intval($_GET['copyshippingregionID'])>0 &&  intval($regionID)>0) {
	// at the moment shipping zones are cross site
	$select = "SELECT ID FROM productshipping WHERE productshipping.regionID = ".intval($regionID);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$numrows = mysql_num_rows($result);
	if($numrows>0) {
		while($row = mysql_fetch_assoc($result)) {
			$newID = duplicateMySQLRecord ("productshipping", $row['ID']);
			$update = "UPDATE productshipping SET createdbyID = 0, createddatetime = '".date('Y-m-d H:i:s')."', regionID = ".intval($_GET['copyshippingregionID'])." WHERE ID = ".$newID;
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		$msg = $numrows." shipping rates have been copied.";
	}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET shippingcalctype=%s, allowcollection=%s, collectionaddress=%s, shippingautocalc=%s, shippingcalcbeforeaddress=%s WHERE ID=%s",
                       GetSQLValueString($_POST['shippingcalctype'], "int"),
                       GetSQLValueString(isset($_POST['allowcollection']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['colectionaddress'], "text"),
                       GetSQLValueString($_POST['shippingautocalc'], "int"),
                       GetSQLValueString(isset($_POST['shippingcalcbeforeaddress']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsShippingRates = 2000;
$pageNum_rsShippingRates = 0;
if (isset($_GET['pageNum_rsShippingRates'])) {
  $pageNum_rsShippingRates = $_GET['pageNum_rsShippingRates'];
}
$startRow_rsShippingRates = $pageNum_rsShippingRates * $maxRows_rsShippingRates;

$varHazardous_rsShippingRates = "-1";
if (isset($_GET['hazardous'])) {
  $varHazardous_rsShippingRates = $_GET['hazardous'];
}
$varRegionID_rsShippingRates = "1";
if (isset($regionID)) {
  $varRegionID_rsShippingRates = $regionID;
}
$varNextDay_rsShippingRates = "-1";
if (isset($_GET['express'])) {
  $varNextDay_rsShippingRates = $_GET['express'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsShippingRates = sprintf("SELECT productshipping.*, region.currencycode, productshippingzone.zonename FROM productshipping LEFT JOIN region ON ( productshipping.regionID = region.ID) LEFT JOIN productshippingzone ON (productshipping.shippingzoneID = productshippingzone.ID) WHERE (%s = 0 OR productshipping.regionID = %s OR productshipping.regionID = 0 ) AND  (%s = -1 OR (productshipping.express = %s)) AND (%s = -1 OR(productshipping.hazardous = %s))", GetSQLValueString($varRegionID_rsShippingRates, "int"),GetSQLValueString($varRegionID_rsShippingRates, "int"),GetSQLValueString($varNextDay_rsShippingRates, "int"),GetSQLValueString($varNextDay_rsShippingRates, "int"),GetSQLValueString($varHazardous_rsShippingRates, "int"),GetSQLValueString($varHazardous_rsShippingRates, "int"));
$query_limit_rsShippingRates = sprintf("%s LIMIT %d, %d", $query_rsShippingRates, $startRow_rsShippingRates, $maxRows_rsShippingRates);
$rsShippingRates = mysql_query($query_limit_rsShippingRates, $aquiescedb) or die(mysql_error());
$row_rsShippingRates = mysql_fetch_assoc($rsShippingRates);

if (isset($_GET['totalRows_rsShippingRates'])) {
  $totalRows_rsShippingRates = $_GET['totalRows_rsShippingRates'];
} else {
  $all_rsShippingRates = mysql_query($query_rsShippingRates);
  $totalRows_rsShippingRates = mysql_num_rows($all_rsShippingRates);
}
$totalPages_rsShippingRates = ceil($totalRows_rsShippingRates/$maxRows_rsShippingRates)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".intval($regionID) ."";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Shipping"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<script>

addListener("load",toggleBeforeAddressKnown);
function toggleBeforeAddressKnown() {
	if(getRadioValue("shippingautocalc")==0) { 
		document.getElementById("beforeAddressKnown").style.color = "#cccccc";
		document.getElementById("shippingcalcbeforeaddress").disabled = true;
	} else {
		document.getElementById("beforeAddressKnown").style.color = "inherit";
		document.getElementById("shippingcalcbeforeaddress").disabled = false;
	}
}
</script>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Shipping</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"> <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Shop</a></li>
      <li class="nav-item"><a href="add_shipping.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add shipping rate</a></li>
      <li class="nav-item"><a href="zones/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Manage Shipping Zones</a></li>
      <li class="nav-item"><a href="information.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Shipping Information</a></li>
     
    </ul></div></nav><?php require_once('../../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-inline">
      <p>
        <label>
      Calculate:
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="0" id="shippingcalctype_0" onclick="this.form.submit()" />
          No shipping
        </label>
        &nbsp;&nbsp;&nbsp;
        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="1" id="shippingcalctype_1" onclick="this.form.submit()" />
          Per basket</label>
        &nbsp;&nbsp;&nbsp;
        <label>
        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"5"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="5" id="shippingcalctype_5" onclick="this.form.submit()" />
          Per item</label>
        &nbsp;&nbsp;&nbsp;
        <label>
        
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="2" id="shippingcalctype_2" onclick="this.form.submit()" />
          Set per product (sum)</label>
        &nbsp;&nbsp;&nbsp;
        
        
        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"4"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="4" id="shippingcalctype_4" onclick="this.form.submit()" />
          Set per product (max)</label>
        &nbsp;&nbsp;&nbsp;
        
        
        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['shippingcalctype'],"3"))) {echo "checked=\"checked\"";} ?> type="radio" name="shippingcalctype" value="3" id="shippingcalctype_3" onclick="this.form.submit()" />
          By weight</label>
        
      </p>
      <p>Rate chosen:
        <label>
          <input type="radio" name="shippingautocalc"  value="0" <?php if ($row_rsProductPrefs['shippingautocalc']==0) {echo "checked=\"checked\"";} ?> onClick="toggleBeforeAddressKnown()">
          by customer</label>
       &nbsp;&nbsp;&nbsp;
        <label>
          <input type="radio" name="shippingautocalc"  value="1" <?php if ($row_rsProductPrefs['shippingautocalc']==1) {echo "checked=\"checked\"";} ?> onClick="toggleBeforeAddressKnown()">
          automatically (but still given choice of standard/express)</label>
        
      
      <label id="beforeAddressKnown"><input <?php if (!(strcmp($row_rsProductPrefs['shippingcalcbeforeaddress'],1))) {echo "checked=\"checked\"";} ?> name="shippingcalcbeforeaddress" id="shippingcalcbeforeaddress" type="checkbox" value="1" >
      show before address known</label><br><br>
      
      
      <input <?php if (!(strcmp($row_rsProductPrefs['allowcollection'],1))) {echo "checked=\"checked\"";} ?> name="allowcollection" type="checkbox" id="allowcollection" value="1" onclick="this.form.submit()" />
      <label>Allow collection from: <input name="colectionaddress" type="text" id="colectionaddress" value="<?php echo $row_rsProductPrefs['collectionaddress']; ?>" size="50" maxlength="255" class="form-control" />
      </label> <input type="hidden" name="MM_update" value="form1" />
       <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>" />    
        <button type="submit" name="savebutton" id="savebutton" class="btn btn-default btn-secondary">Save</button>
     
      </p>
    </form><form  method="get" name="filter" id="filter">
      <fieldset class="form-inline">
        <legend>Filter</legend>
        <select name="hazardous" id="hazardous" onchange="this.form.submit()" class="form-control">
          <option value="-1" <?php if (!(strcmp(-1, @$_GET['hazardous']))) {echo "selected=\"selected\"";} ?>>Hazardous/Non-hazardous</option>
          <option value="0" <?php if (!(strcmp(0, @$_GET['hazardous']))) {echo "selected=\"selected\"";} ?>>Non-hazardous rates ony</option>
          <option value="1" <?php if (!(strcmp(1, @$_GET['hazardous']))) {echo "selected=\"selected\"";} ?>>Hazardous rates only</option>
        </select>
        <select name="express" id="express" onchange="this.form.submit()" class="form-control">
          <option value="-1" <?php if (!(strcmp(-1, @$_GET['express']))) {echo "selected=\"selected\"";} ?>>All delivery</option>
          <option value="0" <?php if (!(strcmp(0, @$_GET['express']))) {echo "selected=\"selected\"";} ?>>Standard delivery only</option>
          <option value="1" <?php if (!(strcmp(1, @$_GET['express']))) {echo "selected=\"selected\"";} ?>>Express delivery</option>
        </select>
      </fieldset>
    </form>
    <?php if ($totalRows_rsShippingRates == 0) { // Show if recordset empty ?>
      <p>There are currently no shipping rates. Start adding using button above.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsShippingRates > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Shipping rates <?php echo ($startRow_rsShippingRates + 1) ?> to <?php echo min($startRow_rsShippingRates + $maxRows_rsShippingRates, $totalRows_rsShippingRates) ?> of <?php echo $totalRows_rsShippingRates ?> </p>
  <table  class="table table-hover"><thead>
    <tr>
      <th>&nbsp;</th>
      <th>Name</th>
      <th>Rate</th>
      <th>Zone</th>
      <th colspan="3">Actions</th>
      </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td class="status<?php echo $row_rsShippingRates['statusID']; ?>">&nbsp;</td>
        <td><?php echo $row_rsShippingRates['shippingname']; ?></td>
        <td><?php echo number_format($row_rsShippingRates['shippingrate'],2,".",","); ?> <?php echo $row_rsShippingRates['currencycode']; ?><?php switch($row_rsShippingRates['ratemultiple']) {			
			case 1 : echo " per item"; break;
			case 2 :  echo " per kg"; break;
			default : echo " flat rate"; break;
		}  echo isset($row_rsShippingRates['minweight']) ? " min ".$row_rsShippingRates['minweight']." kg." : ""; ?><?php echo isset($row_rsShippingRates['maxweight']) ? " max ".$row_rsShippingRates['maxweight']." kg." : ""; ?><?php echo ($row_rsShippingRates['express']==1) ? "<span class=\"nextDay\"> (Next Day)</span>" : ""; ?><?php echo ($row_rsShippingRates['hazardous']==1) ? "<span class=\"hazardous\"> (Hazardous)</span>" : ""; ?><?php echo ($row_rsShippingRates['promotion']==1) ? "<span class=\"promo\"> (Promo)</span>" : ""; ?></td>
        <td><?php echo isset($row_rsShippingRates['zonename']) ? $row_rsShippingRates['zonename'] : "All zones"; ?></td>
        <td><a href="update_shipping.php?shippingrateID=<?php echo $row_rsShippingRates['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        <td><a href="index.php?deleteshippingrateID=<?php echo $row_rsShippingRates['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to delete this shipping rate?');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
        <td><a href="add_shipping.php?basedonshippingID=<?php echo $row_rsShippingRates['ID']; ?>" class="link_add icon_only">Add</a></td>
      </tr>
      <?php } while ($row_rsShippingRates = mysql_fetch_assoc($rsShippingRates)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
  
  <?php if(mysql_num_rows($rsAdminRegions)>1) { 
  mysql_data_seek($rsAdminRegions,0); ?>
  <form method="get" class="form-inline">
  <label>Copy all shipping rates to site:
<select name="copyshippingregionID" class="form-control" >
<option value="" >Choose...</option>
<?php while($adminRegion = mysql_fetch_assoc($rsAdminRegions)) { ?>
<?php if($adminRegion['ID'] != $regionID) { ?>
<option value="<?php echo $adminRegion['ID']; ?>" ><?php echo $adminRegion['title']; ?></option>
<?php } } ?>
</select></label><button class="btn btn-default btn-secondary"  type="submit" onClick="return confirm('Are you sure you eant to copy all shipping rates to a new site?');">Copy</button></form><?php } ?>
  <h2>Important </h2>
  <p>Make sure all options are covered (e.g. weights, distances) in all circumstances or shipping rate could be calculated incorrectly or not at all!</p>
  <h2>Calculation types explained</h2>
  <p>You can choose one caculation type for the site which will determine how shipping is calculated:</p>
  <p><strong>No Shipping </strong> - no shipping will be charged on any order or it is included in price</p>
  <p><strong>Flat rate</strong> - shipping will be charged at flat rate regardless of amount. In addition, alternative rates for distance and express delivery will be taken into account if supplied.</p>
  <p><strong>Per item</strong> - shipping will be charged per item. In addition, alternative rates for distance and express delivery will be taken into account if supplied.</p>
  <p><strong>By weight</strong> - shipping will be calculated by weight. Hazardous and non-hazardous will also be separated if necessary.  In addition, alternative rates for distance and express delivery will be taken into account if supplied.</p>
  <p>&nbsp;</p>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsShippingRates);

mysql_free_result($rsProductPrefs);
?>
