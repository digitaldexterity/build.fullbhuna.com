<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO eventcategory (title, colour, `description`, active, priority) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['colour'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['active'], "int"),
                       GetSQLValueString($_POST['priority'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
}
   
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategoryGroups = "SELECT ID, groupname FROM eventcategorygroup WHERE statusID = 1 ORDER BY groupname ASC";
$rsCategoryGroups = mysql_query($query_rsCategoryGroups, $aquiescedb) or die(mysql_error());
$row_rsCategoryGroups = mysql_fetch_assoc($rsCategoryGroups);
$totalRows_rsCategoryGroups = mysql_num_rows($rsCategoryGroups);
?>
 
<!doctype html>

<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add Diary Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/core/scripts/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet" >
<script src="/core/scripts/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script>
$(function(){
    $('.colorpicker').colorpicker().on('changeColor.colorpicker', function(event){
  		$(this).css("background-color",event.color.toHex());
	});
});
</script>
<style>

<?php if ($totalRows_rsCategoryGroups<1) { echo "
.categoryGroup {
	display:none;
}
"; } ?>
</style>
<?php $_GET['groupID'] = isset($_GET['groupID']) ? $_GET['groupID'] : 1; ?>
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
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar">
      <h1><i class="glyphicon glyphicon-calendar"></i> Add Diary Category</h1>
	 
    
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
        <table class="form-table">
          <tr class="categoryGroup">
            <td class="text-nowrap text-right">Group:</td>
            <td><select name="groupID" id="groupID" class="form-control">
              <option value="1" <?php if (!(strcmp(1, $_GET['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsCategoryGroups['ID']?>"<?php if (!(strcmp($row_rsCategoryGroups['ID'], $_GET['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsCategoryGroups['groupname']?></option>
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
            <td><input type="text"  name="title" class="form-control" /></td>
          </tr>
          <tr>
            <td class="text-nowrap text-right">Colour:</td>
            <td class="form-inline" style="background:<?php echo $row_rsCategory['colour']; ?>"><input name="colour" type="text" id="colour"  class="colorpicker form-control" /></td>
          </tr> 
          <tr>
            <td class="text-nowrap text-right top"><label for="textfield">Priority:</label></td>
            <td class="form-inline">
              <input name="priority" type="text" id="priority" value="1" size="3" maxlength="3" class="form-control"> 
              (optional 1-254)</td>
          </tr>
          <tr>
            <td class="text-nowrap text-right top">Description:</td>
            <td><textarea name="description" cols="50" rows="10" class="form-control"></textarea>            </td>
          </tr> <tr>
            <td class="text-nowrap text-right">&nbsp;</td>
            <td><button type="submit" class="btn btn-primary" >Add category</button></td>
          </tr>
        </table>
      <input type="hidden" name="active" value="1" />
        <input type="hidden" name="MM_insert" value="form1" />
    </form>
     </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategoryGroups);
?>
