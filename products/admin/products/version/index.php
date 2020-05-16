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
  $updateSQL = sprintf("UPDATE productprefs SET versiontitle=%s, versionfilter=%s WHERE ID=%s",
                       GetSQLValueString($_POST['versiontitle'], "text"),
                       GetSQLValueString(isset($_POST['versionfilter']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if(isset($_POST['formaction'])) {
	if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) {
		mysql_select_db($database_aquiescedb, $aquiescedb);	
		if($_POST['formaction'] == "move") {
			foreach($_SESSION['checkbox'] as $key=>$value) {							
 				$select = "SELECT * FROM productversion WHERE ID = ".$value." LIMIT 1";
 				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				// create new finish
				$insert = "INSERT INTO productfinish (finishname, createdbyID, createddatetime) VALUES (".GetSQLValueString($row['versionname'], "text").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
				mysql_query($insert, $aquiescedb) or die(mysql_error());
				$finishID = mysql_insert_id();
				// order
				$update = "UPDATE productfinish SET ordernum = ".$finishID." WHERE ID = ".$finishID;
				mysql_query($update, $aquiescedb) or die(mysql_error());
				// go through products and move from version to finish
				$select = "SELECT productID FROM productwithversion WHERE versionID = ".$value;
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				while($row = mysql_fetch_assoc($result)) {
					// add new relationship
					$insert = "INSERT INTO productwithfinish (finishID, productID, createdbyID, createddatetime) VALUES (".$finishID.",".GetSQLValueString($row['productID'], "int").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
					mysql_query($insert, $aquiescedb) or die(mysql_error());
					// delete old relationship
					$delete = "DELETE FROM productwithversion WHERE productID = ".GetSQLValueString($row['productID'], "int");
					mysql_query($delete, $aquiescedb) or die(mysql_error());
				}
				// finally delete ol dversion
				$delete = "DELETE FROM productversion WHERE ID = ".$value;
				mysql_query($delete, $aquiescedb) or die(mysql_error());
			}
			unset($_SESSION['checkbox']);
			header("location: index.php?msg=".urlencode("Selected items have been moved.")); 
			exit;
		} else { // delete
		
			
			foreach($_SESSION['checkbox'] as $key=>$value) {							
 				$delete = "DELETE FROM productwithversion WHERE versionID = ".$value;
 				mysql_query($delete, $aquiescedb) or die(mysql_error());
				$delete = "DELETE FROM productversion WHERE ID = ".$value;
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




$maxRows_rsVersions = 1000;
$pageNum_rsVersions = 0;
if (isset($_GET['pageNum_rsVersions'])) {
  $pageNum_rsVersions = $_GET['pageNum_rsVersions'];
}
$startRow_rsVersions = $pageNum_rsVersions * $maxRows_rsVersions;

//natural sort
if(isset($_GET['natsort'])) {
	natural_sort_table( "productversion", "versionname", "ordernum") ;
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVersions = "SELECT * FROM productversion ORDER BY ordernum ASC";
$rsVersions = mysql_query($query_rsVersions, $aquiescedb) or die(mysql_error());
$row_rsVersions = mysql_fetch_assoc($rsVersions);
$totalRows_rsVersions = mysql_num_rows($rsVersions);

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
<title><?php $pageTitle = "Manage Product Sizes/Versions"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<?php require_once('../../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../../../css/defaultProducts.css" rel="stylesheet"  />
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
<script>!(jQuery.ui) && document.write('<script src="/3rdparty/jquery/jquery-ui-1.10.1.custom.min.js"><\/script>')</script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            	var order = $(this).sortable('serialize'); 
				$.post( "/core/ajax/sort.ajax.php","table=productversion&"+order, function( data ) {
  						$("#info").html( data );
				});
            } 
        }); 
    }); 
</script>
<?php if(isset($body_class)) $body_class .= " products ";  ?>
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Product Sizes/Versions</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="../categories/index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Categories</a></li>
      <li><a href="add_version.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Version</a></li>
      
    </ul></div></nav><?php require_once('../../../../core/includes/alert.inc.php'); ?>
    
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form"><fieldset class="form-inline"><legend>Options</legend>
  <span id="sprytextfield1">
  <label>version title
    <input name="versiontitle" type="text" id="versiontitle" value="<?php echo $row_rsProductPrefs['versiontitle']; ?>" size="20" maxlength="20" class="form-control">
  </label>
  <span class="textfieldRequiredMsg">A value is required.</span></span>
  <label>
    <input <?php if (!(strcmp($row_rsProductPrefs['versionfilter'],1))) {echo "checked=\"checked\"";} ?> name="versionfilter" type="checkbox" id="versionfilter" value="1">
    Add filter to products index page</label>
  <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">
  <button type="submit" name="savebutton" id="savebutton" class="btn btn-default btn-secondary" >Save</button>
</fieldset>
  <input type="hidden" name="MM_update" value="form">
</form>


    <?php if ($totalRows_rsVersions == 0) { // Show if recordset empty ?>
  <p>There are currenly no product versions. You can add versions (e.g. size or type) when the same product is available in different versions at the same price.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsVersions > 0) { // Show if recordset not empty ?><form action="" method="post" name="form1" id="form1">
      <p class="text-muted">Versions <?php echo ($startRow_rsVersions + 1) ?> to <?php echo min($startRow_rsVersions + $maxRows_rsVersions, $totalRows_rsVersions) ?> of <?php echo $totalRows_rsVersions ?>. (<span id="checkedCount"></span> selected) <span id="info">Drag and drop to sort.</span></p>
     <table class="table table-hover">
     <tbody class="sortable">
        <?php do { ?>
          <tr id="listItem_<?php echo $row_rsVersions['ID']; ?>" >
          <td class="handle"  >&nbsp;</td>
           <td><input type="checkbox" name="checkbox[<?php echo $row_rsVersions['ID']; ?>]" value="<?php echo $row_rsVersions['ID']; ?>" id="checkbox<?php echo $row_rsVersions['ID']; ?>"></td>
            <td class="status<?php echo $row_rsVersions['statusID']; ?>" >&nbsp;</td>
            <td><?php echo $row_rsVersions['versionname']; ?></td>
        
            <td><a href="update_version.php?version=<?php echo $row_rsVersions['ID']; ?>" class="link_edit icon_only">Edit</a></td>
           
        </tr>
          <?php } while ($row_rsVersions = mysql_fetch_assoc($rsVersions)); ?>
      </tbody></table><p>With selected: <select name="formaction">
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
     
   

     
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVersions);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsProductPrefs);
?>
