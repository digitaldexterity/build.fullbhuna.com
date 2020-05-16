<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../inc/product_functions.inc.php'); ?>
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

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.regionID FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

	$uploaded = getUploads();
	if(isset($_POST['noImage'])) {
		$_POST['photoID'] = "";
	}
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			
			addProductGalleryPhoto($_POST['productID'], $uploaded["filename"][0]["newname"], $row_rsLoggedIn['ID']); 
		}
	}
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productoptions SET optionname=%s, productID=%s, finishID=%s, versionID=%s, stockcode=%s, upc=%s, price=%s, weight=%s, photoID=%s, instock=%s, modifiedbyID=%s, modifieddatetime=%s, availabledate=%s WHERE ID=%s",
                       GetSQLValueString($_POST['optionname'], "text"),
                       GetSQLValueString($_POST['productID'], "int"),
                       GetSQLValueString($_POST['finish'], "int"),
                       GetSQLValueString($_POST['version'], "int"),
                       GetSQLValueString($_POST['stockcode'], "text"),
                       GetSQLValueString($_POST['upc'], "text"),
                       GetSQLValueString($_POST['price'], "double"),
                       GetSQLValueString($_POST['weight'], "double"),
                       GetSQLValueString($_POST['photoID'], "int"),
                       GetSQLValueString($_POST['instock'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['availabledate'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "modify_product.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFinishes = "SELECT ID, finishname FROM productfinish ORDER BY finishname ASC";
$rsFinishes = mysql_query($query_rsFinishes, $aquiescedb) or die(mysql_error());
$row_rsFinishes = mysql_fetch_assoc($rsFinishes);
$totalRows_rsFinishes = mysql_num_rows($rsFinishes);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVersions = "SELECT ID, versionname FROM productversion ORDER BY versionname ASC";
$rsVersions = mysql_query($query_rsVersions, $aquiescedb) or die(mysql_error());
$row_rsVersions = mysql_fetch_assoc($rsVersions);
$totalRows_rsVersions = mysql_num_rows($rsVersions);

$varOptionID_rsOption = "-1";
if (isset($_GET['optionID'])) {
  $varOptionID_rsOption = $_GET['optionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOption = sprintf("SELECT productoptions.*, photos.imageURL FROM productoptions LEFT JOIN photos ON (productoptions.photoid = photos.ID) WHERE productoptions.ID = %s", GetSQLValueString($varOptionID_rsOption, "int"));
$rsOption = mysql_query($query_rsOption, $aquiescedb) or die(mysql_error());
$row_rsOption = mysql_fetch_assoc($rsOption);
$totalRows_rsOption = mysql_num_rows($rsOption);

$varProductID_rsPhotos = "-1";
if (isset($row_rsOption['productID'])) {
  $varProductID_rsPhotos = $row_rsOption['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = sprintf("SELECT photos.ID, photos.imageURL  FROM photos LEFT JOIN productgallery ON (productgallery.galleryID = photos.categoryID) WHERE productgallery.productID = %s AND photos.active = 1 ORDER BY photos.ordernum", GetSQLValueString($varProductID_rsPhotos, "int"));
$rsPhotos = mysql_query($query_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);
$totalRows_rsPhotos = mysql_num_rows($rsPhotos);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Edit Option"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../..//css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<style><!--
.photos label {
	display:block;
	
}

.photos label img {
	vertical-align:middle;
	margin: 5px;
}

<?php if($totalRows_rsVersions==0) {
	echo ".version { display: none; } ";
} ?>

<?php if($totalRows_rsFinishes==0) {
	echo ".finish { display: none; } ";
} ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Edit Option</h1>
    <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1">
      <table class="form-table" > <tr>
          <td class="text-nowrap text-right top">Option name:</td>
          <td><input name="optionname" type="text" value="<?php echo htmlentities($row_rsOption['optionname'], ENT_IGNORE, 'UTF-8'); ?>" size="50" maxlength="50"  class="form-control"></td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Price:</td>
          <td class="form-inline"><input name="price" type="text" value="<?php echo isset($row_rsOption['price']) ? htmlentities(number_format($row_rsOption['price'],2,".",""), ENT_COMPAT, 'UTF-8') : ""; ?>" size="10" maxlength="10"   class="form-control"> 
          (optional - if different from standard product price)</td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Weight:</td>
          <td  class="form-inline"><input name="weight" type="text" value="<?php echo ($row_rsOption['weight']>0) ? htmlentities($row_rsOption['weight'], ENT_COMPAT, 'UTF-8') : ""; ?>" size="10" maxlength="10"   class="form-control"></td>
        </tr> <tr>
          <td class="text-nowrap text-right top">Stock code:</td>
          <td><input name="stockcode" type="text" value="<?php echo htmlentities($row_rsOption['stockcode'], ENT_COMPAT, 'UTF-8'); ?>" size="50" maxlength="50"   class="form-control"></td>
        </tr>
        <tr>
          <td class="text-nowrap text-right top">GTIN or UPC:</td>
          <td><input type="text" name="upc" value="<?php echo htmlentities($row_rsOption['upc'], ENT_COMPAT, 'UTF-8'); ?>" size="50"   class="form-control"></td>
        </tr> <tr class="finish">
          <td class="text-nowrap text-right top">Finish:</td>
          <td><select name="finish"   class="form-control">
            <option value="" <?php if (!(strcmp("", htmlentities($row_rsOption['finishID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsFinishes['ID']?>"<?php if (!(strcmp($row_rsFinishes['ID'], htmlentities($row_rsOption['finishID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsFinishes['finishname']?></option>
            <?php
} while ($row_rsFinishes = mysql_fetch_assoc($rsFinishes));
  $rows = mysql_num_rows($rsFinishes);
  if($rows > 0) {
      mysql_data_seek($rsFinishes, 0);
	  $row_rsFinishes = mysql_fetch_assoc($rsFinishes);
  }
?>
          </select></td>
        </tr> <tr class="version">
          <td class="text-nowrap text-right top">Version:</td>
          <td><select name="version"   class="form-control">
            <option value="" <?php if (!(strcmp("", htmlentities($row_rsOption['versionID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsVersions['ID']?>"<?php if (!(strcmp($row_rsVersions['ID'], htmlentities($row_rsOption['versionID'], ENT_COMPAT, 'UTF-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsVersions['versionname']?></option>
            <?php
} while ($row_rsVersions = mysql_fetch_assoc($rsVersions));
  $rows = mysql_num_rows($rsVersions);
  if($rows > 0) {
      mysql_data_seek($rsVersions, 0);
	  $row_rsVersions = mysql_fetch_assoc($rsVersions);
  }
?>
          </select></td>
          </tr> <tr class="photos">
          <td class="top text-right text-nowrap">Select image:</td>
          
          <td> <label><input type="radio" value="" name="photoID" <?php if(!isset($row_rsOption['photoID'])) echo "checked"; ?>>
            None </label><?php if($totalRows_rsPhotos>0) { ?>
            
              <?php do { ?> 
                <label><input type="radio" value="<?php echo $row_rsPhotos['ID']; ?>"  name="photoID" <?php if($row_rsPhotos['ID']==$row_rsOption['photoID']) echo "checked"; ?>><div class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsPhotos['imageURL'],"thumb"); ?>)" ></div><?php echo $row_rsPhotos['title']; ?>
             </label>
                <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?>
          <?php } ?>
        <tr> <tr class="upload">
          <td class=" text-right text-nowrap">Upload new image:</td>
          <td>
          
                <input name="filename" type="file" class="fileinput " accept=".jpg,.jpeg,.gif,.png" id="filename" />
              
           </td>
        <tr>
          <td class="text-nowrap text-right top"><label for="instock">In stock:</label></td>
          <td class="form-inline">
            <input name="instock" type="text" id="instock" value="<?php echo $row_rsOption['instock']; ?>" size="5" maxlength="5"   class="form-control"></td>
        <tr>
          <td class="text-nowrap text-right top">Available date:</td>
          <td class="form-inline"><input name="availabledate" id="availabledate" type="hidden" value="<?php $setvalue =  $row_rsOption['availabledate']; echo $setvalue; $inputname = "availabledate";?>"><?php require_once('../../../core/includes/datetimeinput.inc.php'); ?>
</td>
        <tr> <tr>
          <td class="text-nowrap text-right top">&nbsp;</td>
          <td><button type="submit" class="btn btn-primary">Save changes</button></td>
        </tr>
      </table>
      <input type="hidden" name="ID" value="<?php echo $row_rsOption['ID']; ?>">
      <input type="hidden" name="productID" value="<?php echo htmlentities($row_rsOption['productID'], ENT_COMPAT, 'UTF-8'); ?>">
      <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>">
      <input type="hidden" name="modifieddatetime" value="<?php echo date("Y-m-d H:i:s");  ?>">
      <input type="hidden" name="MM_update" value="form1">
      <input type="hidden" name="ID" value="<?php echo $row_rsOption['ID']; ?>">
    </form>
   
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFinishes);

mysql_free_result($rsVersions);

mysql_free_result($rsOption);

mysql_free_result($rsPhotos);
?>
