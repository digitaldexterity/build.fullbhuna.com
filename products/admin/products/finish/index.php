<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../../core/includes/framework.inc.php'); ?><?php require_once('../../../includes/productFunctions.inc.php'); ?>
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE productprefs SET finishtitle=%s, finishfilter=%s WHERE ID=%s",
                       GetSQLValueString($_POST['finishtitle'], "text"),
                       GetSQLValueString(isset($_POST['finishfilter']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['formaction'])) {
	if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) {
		mysql_select_db($database_aquiescedb, $aquiescedb);	
		if($_POST['formaction'] == "move") {
			foreach($_SESSION['checkbox'] as $key=>$value) {							
 				$select = "SELECT * FROM productfinish WHERE ID = ".$value." LIMIT 1";
 				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				// create new finish
				$insert = "INSERT INTO productversion (versionname, createdbyID, createddatetime) VALUES (".GetSQLValueString($row['finishname'], "text").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
				mysql_query($insert, $aquiescedb) or die(mysql_error());
				$versionID = mysql_insert_id();
				// order
				$update = "UPDATE productversion SET ordernum = ".$versionID." WHERE ID = ".$versionID;
				mysql_query($update, $aquiescedb) or die(mysql_error());
				// go through products and move from version to finish
				$select = "SELECT productID FROM productwithfinish WHERE finishID = ".$value;
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				while($row = mysql_fetch_assoc($result)) {
					// add new relationship
					$insert = "INSERT INTO productwithversion (versionID, productID, createdbyID, createddatetime) VALUES (".$versionID.",".GetSQLValueString($row['productID'], "int").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
					mysql_query($insert, $aquiescedb) or die(mysql_error());
					// delete old relationship
					$delete = "DELETE FROM productwithfinish WHERE productID = ".GetSQLValueString($row['productID'], "int");
					mysql_query($delete, $aquiescedb) or die(mysql_error());
				}
				// finally delete old version
				$delete = "DELETE FROM productfinish WHERE ID = ".$value;
				mysql_query($delete, $aquiescedb) or die(mysql_error());
			}
			unset($_SESSION['checkbox']);
			header("location: index.php?msg=".urlencode("Selected items have been moved.")); 
			exit;
		} else { // delete
		
			
			foreach($_SESSION['checkbox'] as $key=>$value) {							
 				$delete = "DELETE FROM productwithfinish WHERE finishID = ".$value;
 				mysql_query($delete, $aquiescedb) or die(mysql_error());
				$delete = "DELETE FROM productfinish WHERE ID = ".$value;
 				mysql_query($delete, $aquiescedb) or die(mysql_error());
			}
			unset($_SESSION['checkbox']);
			header("location: index.php?msg=".urlencode("Selected items have been deleted.")); 
			exit;
		}
	} else {
		$submit_error = "You must choose at least one item.";
	}
}

//natural sort
if(isset($_GET['natsort'])) {
	natural_sort_table( "productfinish", "finishname", "ordernum") ;
}


$maxRows_rsFinishes = 1000;
$pageNum_rsFinishes = 0;
if (isset($_GET['pageNum_rsFinishes'])) {
  $pageNum_rsFinishes = $_GET['pageNum_rsFinishes'];
}
$startRow_rsFinishes = $pageNum_rsFinishes * $maxRows_rsFinishes;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFinishes = "SELECT * FROM productfinish ORDER BY ordernum  ASC";
$query_limit_rsFinishes = sprintf("%s LIMIT %d, %d", $query_rsFinishes, $startRow_rsFinishes, $maxRows_rsFinishes);
$rsFinishes = mysql_query($query_limit_rsFinishes, $aquiescedb) or die(mysql_error());
$row_rsFinishes = mysql_fetch_assoc($rsFinishes);

if (isset($_GET['totalRows_rsFinishes'])) {
  $totalRows_rsFinishes = $_GET['totalRows_rsFinishes'];
} else {
  $all_rsFinishes = mysql_query($query_rsFinishes);
  $totalRows_rsFinishes = mysql_num_rows($all_rsFinishes);
}
$totalPages_rsFinishes = ceil($totalRows_rsFinishes/$maxRows_rsFinishes)-1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsProductPrefs = "1";
if (isset($_GET['$regionID'])) {
  $colname_rsProductPrefs = $_GET['$regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($colname_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

if(function_exists("saveProductMenu")) { saveProductMenu($regionID); }
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Product Finishes"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<?php require_once('../../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
<script>!(jQuery.ui) && document.write('<script src="/3rdparty/jquery/jquery-ui-1.10.1.custom.min.js"><\/script>')</script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            	var order = $(this).sortable('serialize'); 
				$.post( "/core/ajax/sort.ajax.php","table=productfinish&"+order, function( data ) {
  						$("#info").html( data );
				});
            } 
        }); 
    }); 
</script><?php if(isset($body_class)) $body_class .= " products ";  ?>
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Product Colours/Finishes</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="../categories/index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Categories</a></li>
      <li class="nav-item"><a href="add_finish.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Finish</a></li>
      
    </ul></div></nav><?php require_once('../../../../core/includes/alert.inc.php'); ?><form action="<?php echo $editFormAction; ?>" method="POST" name="form"><fieldset class="form-inline"><legend>Options</legend>
  <span id="sprytextfield1">
  <label>Filter title
    <input name="finishtitle" type="text" id="finishtitle" value="<?php echo $row_rsProductPrefs['finishtitle']; ?>" size="20" maxlength="20" class="form-control">
  </label>
  <span class="textfieldRequiredMsg">A value is required.</span></span>
  <label>
    <input <?php if (!(strcmp($row_rsProductPrefs['finishfilter'],1))) {echo "checked=\"checked\"";} ?> name="finishfilter" type="checkbox" id="finishfilter" value="1">
    Add filter to products index page</label>
  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
  <button type="submit" name="savebutton" id="savebutton" class="btn btn-default btn-secondary" >Save</button>
</fieldset>
  <input type="hidden" name="MM_update" value="form">
</form>
    <?php if ($totalRows_rsFinishes == 0) { // Show if recordset empty ?>
  <p>There are currenly no product finishes. You can add finishes (e.g. colour or fabric) when the same product is available in different finishes at the same price.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsFinishes > 0) { // Show if recordset not empty ?><form action="" method="post" name="form1" id="form1">
      <p class="text-muted">Finishes <?php echo ($startRow_rsFinishes + 1) ?> to <?php echo min($startRow_rsFinishes + $maxRows_rsFinishes, $totalRows_rsFinishes) ?> of <?php echo $totalRows_rsFinishes ?> (<span id="checkedCount"></span> selected) <span id="info">Drag and drop to sort.</span></p>
      <table class="table table-hover"><tbody class="sortable">
        <?php do { ?>
          <tr id="listItem_<?php echo $row_rsFinishes['ID']; ?>" >
          <td class="handle"  >&nbsp;</td><td><input type="checkbox" name="checkbox[<?php echo $row_rsFinishes['ID']; ?>]" value="<?php echo $row_rsFinishes['ID']; ?>" id="checkbox<?php echo $row_rsFinishes['ID']; ?>"></td>
            <td class="status<?php echo $row_rsFinishes['statusID']; ?>">&nbsp;</td>
            <td><span class="fb_avatar" style="background-image:url(<?php if (isset($row_rsFinishes['imageURL'])) {  echo getImageURL($row_rsFinishes['imageURL'],"thumb");  } ?>);"></span></td>
            <td><?php echo $row_rsFinishes['finishname']; ?></td>
            <td><a href="update_finish.php?finish=<?php echo $row_rsFinishes['ID']; ?>" class="link_edit icon_only">Edit</a></td>
        </tr>
          <?php } while ($row_rsFinishes = mysql_fetch_assoc($rsFinishes)); ?>
     </tbody></table><p class="form-inline">With selected: <select name="formaction" class="form-control">
  <option><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
  <option value="delete">Delete</option>
  <option value="move">Move to colours</option>
</select>

  <button type="submit" class="btn btn-default btn-secondary" >Go</button>
  <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
      </p>
</form>
 <p><a href="index.php?natsort=true" onClick="return confirm('This will sort all entries in natural order, i.e. 8, 08x, 9, 10, aa, AB, b, etc.\n\nAny existing sort will be lost.');">Natural Sort All</a></p>
      <?php } // Show if recordset not empty ?>
      <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
      </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsFinishes);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);
?>
