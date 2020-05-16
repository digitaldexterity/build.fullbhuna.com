<?php require_once('../../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../../../includes/productFunctions.inc.php'); ?>
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

function deleteManufacturer($id=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($id>0) {
		$update = "UPDATE product SET manufacturerID = NULL where manufacturerID = ".intval($id);
		mysql_query($update, $aquiescedb) or die(mysql_error());
		$select = "SELECT ID FROM productmanufacturer WHERE subsidiaryofID = ".intval($id);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($row = mysql_fetch_assoc($result)) {
				deleteManufacturer($row['ID']);
			}
		}
		$delete = "DELETE FROM productmanufacturer WHERE ID = ".intval($id);
		mysql_query($delete, $aquiescedb) or die(mysql_error());
	}
}

if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	deleteManufacturer($_GET['deleteID']);
	header("location: index.php"); exit;
}

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE productprefs SET manufacturertitle=%s, manufacturerfilter=%s, manufacturershowsubs=%s WHERE ID=%s",
                       GetSQLValueString($_POST['manufacturertitle'], "text"),
                       GetSQLValueString(isset($_POST['manufacturerfilter']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['manufacturershowsubs']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsManufacturers = 50;
$pageNum_rsManufacturers = 0;
if (isset($_GET['pageNum_rsManufacturers'])) {
  $pageNum_rsManufacturers = $_GET['pageNum_rsManufacturers'];
}
$startRow_rsManufacturers = $pageNum_rsManufacturers * $maxRows_rsManufacturers;


$varRegionID_rsManufacturers = "1";
if (isset($regionID)) {
  $varRegionID_rsManufacturers = $regionID;
}
$varAllSItes_rsManufacturers = "0";
if (isset($_POST['allsites'])) {
  $varAllSItes_rsManufacturers = $_POST['allsites'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsManufacturers = sprintf("SELECT productmanufacturer.*, parent.manufacturername AS parentname, (SELECT COUNT(product.manufacturerID)  FROM product WHERE product.manufacturerID = productmanufacturer.ID) AS products, region.title AS site FROM productmanufacturer LEFT JOIN productmanufacturer AS parent ON (productmanufacturer.subsidiaryofID = parent.ID)  LEFT JOIN region ON (productmanufacturer.regionID = region.ID) WHERE (productmanufacturer.regionID = 0 OR productmanufacturer.regionID = %s OR %s=1) GROUP BY productmanufacturer.ID ORDER BY productmanufacturer.ordernum, productmanufacturer.manufacturername ASC ", GetSQLValueString($varRegionID_rsManufacturers, "int"),GetSQLValueString($varAllSItes_rsManufacturers, "int"));
$rsManufacturers = mysql_query($query_rsManufacturers, $aquiescedb) or die(mysql_error());
$row_rsManufacturers = mysql_fetch_assoc($rsManufacturers);
$totalRows_rsManufacturers = mysql_num_rows($rsManufacturers);

$colname_rsProductPrefs = "1";
if (isset($regionID)) {
  $colname_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT * FROM productprefs WHERE ID = %s", GetSQLValueString($colname_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$queryString_rsManufacturers = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsManufacturers") == false && 
        stristr($param, "totalRows_rsManufacturers") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsManufacturers = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsManufacturers = sprintf("&totalRows_rsManufacturers=%d%s", $totalRows_rsManufacturers, $queryString_rsManufacturers);

if(function_exists("saveProductMenu")) { saveProductMenu($regionID); }

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Manufacturers"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../../css/defaultProducts.css" rel="stylesheet"  />
<link href="../../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script src="../../../../../SpryAssets/SpryValidationTextField.js"></script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=productmanufacturer&"+order); 
            } 
        }); 
    }); 
</script>
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
    <!-- InstanceBeginEditable name="Body" --><div id="pageManageManufacturers"><?php require_once('../../../../../core/region/includes/chooseregion.inc.php'); ?>
              <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Manufacturers</h1>
              
              <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Categories</a></li>
                <li class="nav-item"><a href="add_manufacturer.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Manufacturer</a></li>
                
              </ul></div></nav> <form action="<?php echo $editFormAction; ?>" method="POST" name="form"><fieldset class="form-inline"><legend>Options</legend>
                  <span id="sprytextfield1">
                  <label>Manufacturer title
                    <input name="manufacturertitle" type="text" id="manufacturertitle" value="<?php echo $row_rsProductPrefs['manufacturertitle']; ?>" size="20" maxlength="20" class="form-control">
                  </label>
                  <span class="textfieldRequiredMsg">A value is required.</span></span>
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['manufacturerfilter'],1))) {echo "checked=\"checked\"";} ?> name="manufacturerfilter" type="checkbox" id="manufacturerfilter" value="1">
                Add filter to products index page</label>&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['manufacturershowsubs'],1))) {echo "checked=\"checked\"";} ?> name="manufacturershowsubs" type="checkbox" id="manufacturershowsubs" value="1">
                  Show sub brands in menus/filters</label>&nbsp;&nbsp;
                  
                  <label class="rank10">
                  <input <?php if (isset($_POST['allsites'])) {echo "checked=\"checked\"";} ?> name="allsites" type="checkbox" id="allsites" value="1">
                WADMIN: Show all sites</label>
               
                
                
                <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
                <button type="submit" name="savebutton" id="savebutton" class="btn btn-default btn-secondary" >Save</button>
  </fieldset>
                <input type="hidden" name="MM_update" value="form">
  </form>
         <?php require_once('../../../../../core/includes/alert.inc.php'); ?>
     
              <?php if ($totalRows_rsManufacturers == 0) { // Show if recordset empty ?>
              <p>There are currently no manufacturers in the system.</p>
              <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsManufacturers > 0) { // Show if recordset not empty ?>
    <p class="text-muted">Manufacturers <?php echo ($startRow_rsManufacturers + 1) ?> to <?php echo min($startRow_rsManufacturers + $maxRows_rsManufacturers, $totalRows_rsManufacturers) ?> of <?php echo $totalRows_rsManufacturers ?>. <span id="info">Drag and drop to sort.</span></p>
  
      
   <table class="table table-hover">
   <thead>
<tr><th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>Manufacturer</th>
        <th>&nbsp;</th>
        <th>Number</th>
        <th>Site</th>
        <th>&nbsp;</th> <th>&nbsp;</th>
      </tr></thead><tbody class="sortable">
      <?php do { ?>
         <tr  id="listItem_<?php echo $row_rsManufacturers['ID']; ?>"> 
         <td class="handle"  data-toggle="tooltip" data-placement="right" title="Drag and drop order of pages">&nbsp;</td>
         
         
          <td class="status<?php echo $row_rsManufacturers['statusID']; ?>">&nbsp;</td>
          <td><span class="fb_avatar"  style="background-image:url(<?php echo getImageURL($row_rsManufacturers['imageURL'],"thumb"); ?>); width:32px; height:32px; vertical-align:
middle;" ></span></td>
          <td><a href="update_manufacturer.php?manufacturerID=<?php echo $row_rsManufacturers['ID']; ?>" ><?php echo $row_rsManufacturers['manufacturername']; ?></a><?php echo ($row_rsManufacturers['regionID']==0) ? " (all sites)" : ""; ?> <?php if($row_rsManufacturers['manufacturersale']==1) { ?>
                <img src="/core/images/icons/flag_red.png" alt="Sale item" width="16" height="16" style="vertical-align:
middle;" />
                <?php } ?></td>
          <td><?php echo isset($row_rsManufacturers['parentname']) ? "(Parent: ".$row_rsManufacturers['parentname'].")" : ""; ?></td>
          <td><a href="/products/admin/products/index.php?search=&categoryID=0&manufacturerID=<?php echo $row_rsManufacturers['ID']; ?>">(<?php echo $row_rsManufacturers['products']; ?>)</a></td>
          <td><em><?php echo isset($row_rsManufacturers['site']) ? $row_rsManufacturers['site'] : "All sites"; ?></em></td>
          
          <td><a href="update_manufacturer.php?manufacturerID=<?php echo $row_rsManufacturers['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          <td><a href="index.php?deleteID=<?php echo $row_rsManufacturers['ID']; ?>" onClick="return confirm('Are you sure you want to delete this manufacturer?\n\nAll sub-brands will also be removed and products unlinked')" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>  
        </tr>
        <?php } while ($row_rsManufacturers = mysql_fetch_assoc($rsManufacturers)); ?>
     </tbody></table>
    <br />
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsManufacturers > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsManufacturers=%d%s", $currentPage, 0, $queryString_rsManufacturers); ?>">First</a>
          <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsManufacturers > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsManufacturers=%d%s", $currentPage, max(0, $pageNum_rsManufacturers - 1), $queryString_rsManufacturers); ?>">Previous</a>
          <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsManufacturers < $totalPages_rsManufacturers) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsManufacturers=%d%s", $currentPage, min($totalPages_rsManufacturers, $pageNum_rsManufacturers + 1), $queryString_rsManufacturers); ?>">Next</a>
          <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsManufacturers < $totalPages_rsManufacturers) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsManufacturers=%d%s", $currentPage, $totalPages_rsManufacturers, $queryString_rsManufacturers); ?>">Last</a>
          <?php } // Show if not last page ?></td>
        </tr>
      </table>
  <?php } // Show if recordset not empty ?></div>
            <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
            </script>
          <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsManufacturers);

mysql_free_result($rsProductPrefs);
?>
