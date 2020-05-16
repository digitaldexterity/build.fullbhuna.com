<?php require_once('../../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../../../core/includes/upload.inc.php'); ?><?php require_once('../../../../../core/includes/framework.inc.php'); ?><?php require_once('../../../../includes/productFunctions.inc.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$uploaded = getUploads();
		if(isset($_POST['noImage'])) {
		$_POST['imageURL'] = "";
	}
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}		
	}
	$_POST['longID'] = createURLname($_POST['longID'], $_POST['manufacturername'], "-",  "productmanufacturer",$_POST['ID']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productmanufacturer SET longID=%s, subsidiaryofID=%s, exclpromos=%s, manufacturername=%s, manufacturershipping=%s, `description`=%s, imageURL=%s, regionID=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, manufacturersale=%s, manufactureremail=%s, freesamplesku=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['subsidiaryofID'], "int"),
                       GetSQLValueString(isset($_POST['exclpromos']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['manufacturername'], "text"),
                       GetSQLValueString($_POST['manufacturershipping'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['manufacturersale']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['manufactureremail'], "text"),
                       GetSQLValueString($_POST['freesamplesku'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	if(isset($_POST['mindeliverytime']) && intval($_POST['mindeliverytime']) >0 && isset($_POST['maxdeliverytime']) && intval($_POST['maxdeliverytime']) >0) {
		$select = "SELECT product.ID FROM product LEFT JOIN productinregion ON (productinregion.productID = product.ID) WHERE (".GetSQLValueString($_POST['regionID'], "int")." = 0 OR product.regionID = 0 OR productinregion.regionID = ".GetSQLValueString($_POST['regionID'], "int")." OR  product.regionID = ".GetSQLValueString($_POST['regionID'], "int").") AND  manufacturerID = ".GetSQLValueString($_POST['ID'], "int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($row = mysql_fetch_assoc($result)) {
				$update = "UPDATE product SET mindeliverytime = ".GetSQLValueString($_POST['mindeliverytime'], "int").", maxdeliverytime = ".GetSQLValueString($_POST['maxdeliverytime'], "int").", deliveryperiod = ".GetSQLValueString($_POST['deliveryperiod'], "int")." WHERE ID = ".$row['ID'];
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}
		}
		
	}
	
	if(isset($_POST['suspend'])) {
		$update = "UPDATE product SET statusID = 0, custompageURL = ".GetSQLValueString($_POST['redirectURL'], "text")." WHERE manufacturerID = ".GetSQLValueString($_POST['ID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error());
		$rows = mysql_affected_rows();
		$msg = $rows." products were suspended from sale.";
	}
  $updateGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] :  "index.php";
  $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
  $updateGoTo .= isset($msg) ? "msg=".urlencode($msg)."&" : "";
  if (isset($_SERVER['QUERY_STRING'])) {
    
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}


$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsManufacturer = "-1";
if (isset($_GET['manufacturerID'])) {
  $colname_rsManufacturer = $_GET['manufacturerID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturer = sprintf("SELECT * FROM productmanufacturer WHERE ID = %s", GetSQLValueString($colname_rsManufacturer, "int"));
$rsManufacturer = mysql_query($query_rsManufacturer, $aquiescedb) or die(mysql_error());
$row_rsManufacturer = mysql_fetch_assoc($rsManufacturer);
$totalRows_rsManufacturer = mysql_num_rows($rsManufacturer);

$varManufacturerID_rsManufacturers = "-1";
if (isset($_GET['manufacturerID'])) {
  $varManufacturerID_rsManufacturers = $_GET['manufacturerID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = sprintf("SELECT * FROM productmanufacturer WHERE ID != %s ORDER BY manufacturername ASC", GetSQLValueString($varManufacturerID_rsManufacturers, "int"));
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Manufacturer"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../../../css/defaultProducts.css" rel="stylesheet"  /><style><!--<?php if($totalRows_rsManufacturers<1) { 
echo ".subsidiary { display: none; } ";
}

if($totalRows_rsRegion==0 || $row_rsLoggedIn['usertypeID'] <9 ) {
	echo ".region { display: none; } ";
}

?>
--></style>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  <h1><i class="glyphicon glyphicon-shopping-cart"></i> Update Manufacturer</h1>
  
  <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
    <table class="form-table"> <tr>
        <td class="text-nowrap text-right">Manufacturer:</td>
        <td><span id="sprytextfield1">
          <input name="manufacturername" type="text"  value="<?php echo $row_rsManufacturer['manufacturername']; ?>" size="50" maxlength="100"  class="form-control" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
      </tr>
      <tr class="subsidiary">
        <td class="text-nowrap text-right"><label for="subsidiaryofID">Subsidiary of:</label></td>
        <td><select name="subsidiaryofID" id="subsidiaryofID"  class="form-control" >
          <option value="" <?php if (!(strcmp("", $row_rsManufacturers['subsidiaryofID']))) {echo "selected=\"selected\"";} ?>>None</option>
          <?php $rows = mysql_num_rows($rsManufacturers);
  if($rows > 0) {
do {  
?>
          <option value="<?php echo $row_rsManufacturers['ID']?>"<?php if (!(strcmp($row_rsManufacturers['ID'], $row_rsManufacturer['subsidiaryofID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsManufacturers['manufacturername']?></option>
          <?php
} while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers));
 
      mysql_data_seek($rsManufacturers, 0);
	  $row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
  }
?>
          </select></td>
      </tr>
      <tr class="region">
        <td class="text-nowrap text-right"><label for="regionID">Site:</label></td>
        <td><select name="regionID" id="regionID"  class="form-control" >
          <option value="0">All sites</option>
          <?php if($totalRows_rsRegion>0) {
do {  
?>
          <option value="<?php echo $row_rsRegion['ID']?>" <?php if($row_rsManufacturer['regionID'] == $row_rsRegion['ID']) echo "selected"; ?>><?php echo $row_rsRegion['title']; ?></option>
          <?php
} while ($row_rsRegion = mysql_fetch_assoc($rsRegion));
  $rows = mysql_num_rows($rsRegion);
  if($rows > 0) {
      mysql_data_seek($rsRegion, 0);
	  $row_rsRegion = mysql_fetch_assoc($rsRegion);
  }
			}
?>
        </select></td>
      </tr> <tr>
        <td class="text-nowrap text-right top"><label for="filename">Image:</label></td>
        <td><?php if (isset($row_rsManufacturer['imageURL'])) { ?>
          <img src="<?php echo getImageURL($row_rsManufacturer['imageURL'],"medium"); ?>" alt="Current image" />
          <label> <input name="noImage" type="checkbox" value="1" />
            Remove image</label><br />
          <?php } ?>
          <span class="upload">
            <input name="filename" type="file"  id="filename" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
            </span>
          <input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsManufacturer['imageURL']; ?>"></td>
      </tr> <tr>
        <td class="text-nowrap text-right top"><label for="description">Description:</label></td>
        <td><textarea name="description" cols="60" rows="5" id="description" class="tinymce form-control" ><?php echo $row_rsManufacturer['description']; ?></textarea></td>
      </tr> 
      <tr>
        <td class="text-nowrap text-right"><label for="manufacturershipping" title="This text will appear on product page and basket to describe any shipping information specific to this supplier" data-toggle="tooltip">Shipping info:</label></td>
        <td><input name="manufacturershipping" type="text" id="manufacturershipping" value="<?php echo $row_rsManufacturer['manufacturershipping']; ?>" size="50" maxlength="100" placeholder="(Optional - e.g. usually ships in 5 days)"  class="form-control" ></td>
      </tr>
      <tr>
        <td class="text-nowrap text-right"><label for="mindeliverytime" title="These times will be copied to all products that are currently related to this supplier. Individual products can then be edited seprately." data-toggle="tooltip">Delivery lead times:</label></td>
        <td class="form-inline">Between
                  <input name="mindeliverytime" id="mindeliverytime" value="" size="5" maxlength="5"  class="form-control"  >
                 and 
                
                  <input name="maxdeliverytime" id="maxdeliverytime" value="" size="5" maxlength="5"  class="form-control" >
                
                
                  <select name="deliveryperiod" id="deliveryperiod"  class="form-control" >
                    <option value="1" >hours</option>
                    <option value="24" selected >working days</option>
                    <option value="168" >weeks</option>
                  </select></td>
      </tr>
      <tr>
        <td class="text-nowrap text-right"><label for="longID">URL name:</label></td>
        <td>
          <input name="longID" type="text" id="longID" value="<?php echo $row_rsManufacturer['longID']; ?>" size="50" maxlength="255"  class="form-control" ></td>
      </tr> <tr>
        <td class="text-nowrap text-right"><label for="manufacturersale">On sale:</label></td>
        <td><input <?php if (!(strcmp($row_rsManufacturer['manufacturersale'],1))) {echo "checked=\"checked\"";} ?> name="manufacturersale" type="checkbox" id="manufacturersale" value="1"> &nbsp;&nbsp;&nbsp; <label for="exclpromos">Excluded from all promotions</label><input <?php if (!(strcmp($row_rsManufacturer['exclpromos'],1))) {echo "checked=\"checked\"";} ?> name="exclpromos" type="checkbox" id="exclpromos" value="1">
          </td>
      </tr>
      <tr>
        <td class="text-nowrap text-right"><label for="manufactureremail">Supplier Order Email:</label></td>
        <td>
          <input name="manufactureremail" type="text" class="form-control" id="manufactureremail" placeholder="Dropship email (leave blank to exclude)" value="<?php echo $row_rsManufacturer['manufactureremail']; ?>"></td>
      </tr> 
      <tr>
        <td class="text-nowrap text-right">Free sample SKU:
          </td><td> <input name="freesamplesku" type="text" class="form-control" id="freesamplesku" placeholder="" value="<?php echo $row_rsManufacturer['freesamplesku']; ?>"></td>
      </tr> <tr>
        <td class="text-nowrap text-right">Active:</td>
        <td><input <?php if (!(strcmp($row_rsManufacturer['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /></td>
      </tr> 
      <tr>
        <td class="text-nowrap text-right"><label for="suspend">Suspend:</label></td>
        <td class="form-inline"><input <?php if (isset($_POST['suspend'])) {echo "checked=\"checked\"";} ?> name="suspend" type="checkbox" id="suspend" value="1" onClick="if(this.checked) { alert('This will make all products from this manufacturer inactive. You can optionally add a redirect URL for links to these products.') }" /> Remove products from sale and add redirect to: <input name="redirectURL" type="text" class="form-control" placeholder="Optional URL"></td>
      </tr>
      <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
      </tr>
    </table>
    <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
    <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
    <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsManufacturer['ID']; ?>" />
    <input type="hidden" name="MM_update" value="form1" />
  </form>
  <p>&nbsp;</p>
<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
  </script>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsManufacturer);

mysql_free_result($rsManufacturers);

mysql_free_result($rsRegion);
?>
