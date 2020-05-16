<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php
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
?><?php
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
  $updateSQL = sprintf("UPDATE eventcategory SET title=%s, colour=%s, `description`=%s, active=%s, priority=%s WHERE ID=%s",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['colour'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString(isset($_POST['active']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['priority'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

$colname_rsCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsCategory = (get_magic_quotes_gpc()) ? $_GET['categoryID'] : addslashes($_GET['categoryID']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT * FROM eventcategory WHERE ID = %s", $colname_rsCategory);
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategoryGroups = "SELECT ID, groupname FROM eventcategorygroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsCategoryGroups = mysql_query($query_rsCategoryGroups, $aquiescedb) or die(mysql_error());
$row_rsCategoryGroups = mysql_fetch_assoc($rsCategoryGroups);
$totalRows_rsCategoryGroups = mysql_num_rows($rsCategoryGroups);
?><!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Diary Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>

<?php if ($totalRows_rsCategoryGroups<1) { echo "
.categoryGroup {
	display:none;
}
"; } ?>
</style>
<link href="/core/scripts/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet" >
<script src="/core/scripts/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script>
$(function(){
    $('.colorpicker').colorpicker().on('changeColor.colorpicker', function(event){
  		$(this).css("background-color",event.color.toHex());
	});
});
</script>
<link href="../../css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page calendar">   <h1><i class="glyphicon glyphicon-calendar"></i> Update Diary Category</h1>
    
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
        <table class="form-table">
          <tr class="categoryGroup">
            <td class="text-nowrap text-right">Group:</td>
            <td><select name="groupID" id="groupID" class="form-control">
              <option value="1" <?php if (!(strcmp(1, $row_rsCategory['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsCategoryGroups['ID']?>"<?php if (!(strcmp($row_rsCategoryGroups['ID'], $row_rsCategory['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategoryGroups['groupname']?></option>
              <?php
} while ($row_rsCategoryGroups = mysql_fetch_assoc($rsCategoryGroups));
  $rows = mysql_num_rows($rsCategoryGroups);
  if($rows > 0) {
      mysql_data_seek($rsCategoryGroups, 0);
	  $row_rsCategoryGroups = mysql_fetch_assoc($rsCategoryGroups);
  }
?>
            </select></td>
          </tr> <tr>
            <td class="text-nowrap text-right">Title:</td>
            <td><input type="text"  name="title" value="<?php echo $row_rsCategory['title']; ?>"  class="form-control" /></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right">Colour:</td>
            <td class="form-inline" style="background:<?php echo $row_rsCategory['colour']; ?>"><input name="colour" type="text" id="colour" value="<?php echo $row_rsCategory['colour']; ?>" class="colorpicker form-control" />
			
</td>
          </tr> 
          <tr>
            <td class="text-nowrap text-right top"><label for="textfield">Priority:</label></td>
            <td class="form-inline"><input name="priority" type="text" id="priority" value="<?php echo $row_rsCategory['priority']; ?>" size="3" maxlength="3" class="form-control">
              (optional 1-254)</td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td><textarea name="description" cols="50" rows="10" class="form-control"><?php echo $row_rsCategory['description']; ?></textarea>            </td>
          </tr> <tr>
            <td class="text-nowrap text-right">Active:</td>
            <td><input type="checkbox" name="active" value="1" <?php if (!(strcmp($row_rsCategory['active'],1))) {echo "checked=\"checked\"";} ?> /></td>
          </tr> <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary" >Update category</button></td>
          </tr>
        </table>
    <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
    </form>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategory);

mysql_free_result($rsCategoryGroups);
?>