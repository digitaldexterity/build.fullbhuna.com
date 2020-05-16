<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../includes/productFunctions.inc.php'); ?><?php require_once('../../../../core/includes/framework.inc.php'); ?>
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

if ((isset($_GET["deleteID"])) && intval($_GET["deleteID"])>0) {
	$delete = "DELETE FROM producttag WHERE ID = ".intval($_GET["deleteID"]);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}

if(isset($_POST['tag']) && count($_POST['tag'])>1) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$keepID = 0;
	foreach($_POST['tag'] as $key => $value) {
		if($keepID == 0) { // none to keep as yet
			$keepID = intval($key);
		} else { // merge to keep
			$update = "UPDATE producttagged SET tagID = ".$keepID. " WHERE tagID = ".intval($key);
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
			//echo $update."<br>";
			$delete = "DELETE FROM producttag WHERE ID = ".intval($key);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			//echo $delete."<br>";
		}		
	}
	$msg = "Tags successfully merged";
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	// new category?
	if(isset($_POST['taggroupname']) && trim($_POST['taggroupname'])!="") {
		$insert = "INSERT INTO producttaggroup (taggroupname, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['taggroupname'], "text").",".GetSQLValueString($_POST['regionID'], "int").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
		mysql_select_db($database_aquiescedb, $aquiescedb);
  		$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
		$_POST['taggroupID'] = mysql_insert_id();
		$update = "UPDATE producttaggroup SET ordernum = ".$_POST['taggroupID']." WHERE ID = ".$_POST['taggroupID'];
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	
	$_POST['taggroupID'] = ($_POST['taggroupID'] == "-1") ? "" : $_POST['taggroupID'];


}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO producttag (tagname, taggroupID, createdbyID, createddatetime, regionID) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['tagname'], "text"),
                       GetSQLValueString($_POST['taggroupID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['regionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE productprefs SET tagsearchtype=%s WHERE ID=%s",
                       GetSQLValueString($_POST['tagsearchtype'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsTagGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsTagGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTagGroups = sprintf("SELECT ID, taggroupname FROM producttaggroup WHERE (regionID = %s OR %s = 0) ORDER BY taggroupname ASC", GetSQLValueString($varRegionID_rsTagGroups, "int"),GetSQLValueString($varRegionID_rsTagGroups, "int"));
$rsTagGroups = mysql_query($query_rsTagGroups, $aquiescedb) or die(mysql_error());
$row_rsTagGroups = mysql_fetch_assoc($rsTagGroups);
$totalRows_rsTagGroups = mysql_num_rows($rsTagGroups);

$varRegionID_rsTags = "1";
if (isset($regionID)) {
  $varRegionID_rsTags = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTags = sprintf("SELECT producttag.ID, tagname, taggroupID, producttaggroup.taggroupname, producttag.taggroupID, COUNT(producttagged.ID) AS tagged, producttaggroup.regionID FROM producttag LEFT JOIN producttaggroup ON (producttag.taggroupID = producttaggroup.ID) LEFT JOIN producttagged ON (producttag.ID = producttagged.tagID) WHERE producttaggroup.regionID = %s GROUP BY producttag.ID  ORDER BY producttaggroup.ordernum, producttag.ordernum, producttaggroup.ID", GetSQLValueString($varRegionID_rsTags, "int"));
$rsTags = mysql_query($query_rsTags, $aquiescedb) or die(mysql_error());
$row_rsTags = mysql_fetch_assoc($rsTags);
$totalRows_rsTags = mysql_num_rows($rsTags);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT ID, tagsearchtype FROM productprefs WHERE ID = ".$regionID;
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
<title>
<?php $pageTitle = "Manage Product Tags"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/defaultProducts.css" rel="stylesheet" >
<style><!--
#taggroupname {
	display:none;
}
--></style>
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
<script>!(jQuery.ui) && document.write('<script src="/3rdparty/jquery/jquery-ui-1.10.1.custom.min.js"><\/script>')</script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    
	
	$(document).ready(function() { 
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=producttag&"+order); 
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Tags</h1>
    
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../categories/index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Categories</a></li>
      <li><a href="taggroups/index.php"  class="link_manage"><i class="glyphicon glyphicon-cog"></i> Tag Groups</a></li>
    </ul></div></nav><?php require_once('../../../../core/includes/alert.inc.php'); ?>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form2">
      <p>When searching by tag, show products that:
        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['tagsearchtype'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="tagsearchtype" value="1" id="tagsearchtype_0" onChange="this.form.submit()">
          match any</label>
&nbsp;&nbsp;&nbsp;        <label>
          <input <?php if (!(strcmp($row_rsProductPrefs['tagsearchtype'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="tagsearchtype" value="2" id="tagsearchtype_1"  onChange="this.form.submit()">
          match all</label>
        <input type="hidden" name="ID" id="ID" value="<?php echo $row_rsProductPrefs['ID']; ?>">&nbsp;&nbsp;&nbsp;<span id="info">Drag and drop to sort.</span>
       
      </p>
      <input type="hidden" name="MM_update" value="form2">
    </form>
    
    <form action="<?php echo $editFormAction; ?>" method="POST" name="form1">
      <fieldset class="form-inline">
        <legend>Add</legend>
        <label>Add tag 
          <input name="tagname" type="text" id="tagname" size="20" maxlength="50" class="form-control"></label>
         <label>to  group 
       
          <select name="taggroupID" id="taggroupID" onChange="if(this.value== -1) { document.getElementById('taggroupname').style.display = 'inline'; } else { document.getElementById('taggroupname').style.display = 'none'; document.getElementById('taggroupname').value = ''; }"  class="form-control">
            <option value="" <?php if (!(strcmp(0, @$_GET['taggroupID']))) {echo "selected=\"selected\"";} ?>>Choose..</option>
            <option value="-1" <?php if (!(strcmp(-1, @$_GET['taggroupID']))) {echo "selected=\"selected\"";} ?>>Add new group...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsTagGroups['ID']?>"<?php if (!(strcmp($row_rsTagGroups['ID'], @$_GET['taggroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTagGroups['taggroupname']?></option>
            <?php
} while ($row_rsTagGroups = mysql_fetch_assoc($rsTagGroups));
  $rows = mysql_num_rows($rsTagGroups);
  if($rows > 0) {
      mysql_data_seek($rsTagGroups, 0);
	  $row_rsTagGroups = mysql_fetch_assoc($rsTagGroups);
  }
?>
          </select>
        </label>
         <input name="taggroupname" type="text" id="taggroupname" size="20" maxlength="30"  class="form-control">
         <button type="submit" class="btn btn-default btn-secondary"  >Add</button>
         <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
         <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
       
         <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>">
      </fieldset>
      <input type="hidden" name="MM_insert" value="form1">
    </form>
    <form method="post" name="tags" >
    <?php if ($totalRows_rsTags == 0) { // Show if recordset empty ?>
  <p>Create tags to tag to any product and divide these tags into groups so that customers can filter products, e.g by age, gender, occasion, etc.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsTags > 0) { // Show if recordset not empty ?>
     <table class="table table-hover">
     
       <thead><tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>Tag</th>
          <th>Group</th>
          <th>Tagged</th>
          <th>&nbsp;</th><th>&nbsp;</th>
          
       </tr></thead><tbody class="sortable">
        <?php $prev_taggroup = 0; do { if($prev_taggroup !=0 && $prev_taggroup !=$row_rsTags['taggroupID']) { echo "</tbody><tbody class = \"sortable\" >";  }$prev_taggroup =$row_rsTags['taggroupID']; ?>
          <tr id="listItem_<?php echo $row_rsTags['ID']; ?>"><td class="handle"  >&nbsp;</td>
            <td>
              <input type="checkbox" name="tag[<?php echo $row_rsTags['ID']; ?>]" value="<?php echo $row_rsTags['ID']; ?>">
             
           </td>
          
            <td><?php echo $row_rsTags['tagname']; ?></td>
            <td><?php echo $row_rsTags['taggroupname']; if($row_rsTags['regionID']==0) { ?>(All sites)<?php } ?></td>
            <td>(<?php echo $row_rsTags['tagged']; ?>)</td> 
            <td><a href="update_tag.php?tagID=<?php echo $row_rsTags['ID']; ?>" class="link_edit icon_only">Edit</a></td>
            <td><a href="index.php?deleteID=<?php echo $row_rsTags['ID']; ?>" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
         </tr>
          <?php } while ($row_rsTags = mysql_fetch_assoc($rsTags)); ?>
     </tbody></table>
  <button  type="submit"  class="btn btn-default btn-secondary">Merge Selected</button>
    <?php } // Show if recordset not empty ?></form>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTagGroups);

mysql_free_result($rsTags);

mysql_free_result($rsProductPrefs);
?>
