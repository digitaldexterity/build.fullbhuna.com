<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = "SELECT * FROM article";
$rsArticles = mysql_query($query_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);
$totalRows_rsArticles = mysql_num_rows($rsArticles);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = "SELECT * FROM product";
$rsProducts = mysql_query($query_rsProducts, $aquiescedb) or die(mysql_error());
$row_rsProducts = mysql_fetch_assoc($rsProducts);
$totalRows_rsProducts = mysql_num_rows($rsProducts);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductDetails = "SELECT * FROM productdetails";
$rsProductDetails = mysql_query($query_rsProductDetails, $aquiescedb) or die(mysql_error());
$row_rsProductDetails = mysql_fetch_assoc($rsProductDetails);
$totalRows_rsProductDetails = mysql_num_rows($rsProductDetails);
?>
<?php if(isset($_POST['newdomain'])) {
	$count = 0;
	
	
	 if($totalRows_rsArticles>0) {
		 do { 
	 $newbody = str_replace($_POST['olddomain'],$_POST['newdomain'],$row_rsArticles['body']); 
	  $update = "UPDATE article SET body = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsArticles['ID'];	 
	  $result = mysql_query($update, $aquiescedb) or die( $update." ".mysql_error());
	  $count = $count + mysql_affected_rows();
 } while ($row_rsArticles = mysql_fetch_assoc($rsArticles));
 }
 
 
 
 	  if($totalRows_rsProducts>0) {
		  do { 
	 $newbody = str_replace($_POST['olddomain'],$_POST['newdomain'],$row_rsProducts['description']); 
	  $update = "UPDATE products SET description = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsProducts['ID'];	 
	  $result = mysql_query($update, $aquiescedb) or die($update." ".mysql_error());
	  $count = $count + mysql_affected_rows();
 } while ($row_rsProducts = mysql_fetch_assoc($rsProducts));
	  }
 
 
 
 	 if($totalRows_rsProductDetails>0) {
		 do { 
	 $newbody = str_replace($_POST['olddomain'],$_POST['newdomain'],$row_rsProductDetails['tabtext']); 
	  $update = "UPDATE productdetails SET tabtext = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsProductDetails['ID'];	 
	  $result = mysql_query($update, $aquiescedb) or die($update." ".mysql_error());
	  $count = $count + mysql_affected_rows();
 } while ($row_rsProductDetails = mysql_fetch_assoc($rsProductDetails));
	 }
 
 
 $alert = "Complete. Updated pages: ".$count;
}

?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - Update Domain LInks</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Update Domain Links</h1>
<?php if(isset($alert)) { ?>
<p class="alert warning alert-warning" role="alert"><?php echo $alert; ?></p>
<?php } else { ?>
<p>This page will update absolute links in your artciles pages when you move from one domoan to another.</p><?php } ?>
<form action="" method="post" id="form1">
<table border="0" cellpadding="0" cellspacing="0" class="form-table">
  <tr>
    <td>Old domain:</td>
    <td><span id="sprytextfield1">
      <input name="olddomain" type="text" id="olddomain" size="50" maxlength="50" />
      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
  </tr>
  <tr>
    <td>New domain:</td>
    <td><span id="sprytextfield2">
      <input name="newdomain" type="text" id="newdomain" size="50" maxlength="50" value="<?php echo isset($_POST['newdomain']) ? $_POST['newdomain'] : $_SERVER['HTTP_HOST']; ?>" />
      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input name="update" type="submit" class="button" id="update" value="Update" /></td>
  </tr>
</table>
</form>


<script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"e.g. www.olddomain.com"});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none");
//-->
</script><!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticles);

mysql_free_result($rsProducts);

mysql_free_result($rsProductDetails);
?>
