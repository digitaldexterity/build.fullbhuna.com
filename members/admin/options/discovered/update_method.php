<?php require_once('../../../../Connections/aquiescedb.php'); ?>
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
  $updateSQL = sprintf("UPDATE discovered SET `description`=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsDiscoverMethod = "-1";
if (isset($_GET['methodID'])) {
  $colname_rsDiscoverMethod = $_GET['methodID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscoverMethod = sprintf("SELECT * FROM discovered WHERE ID = %s ORDER BY ordernum", GetSQLValueString($colname_rsDiscoverMethod, "int"));
$rsDiscoverMethod = mysql_query($query_rsDiscoverMethod, $aquiescedb) or die(mysql_error());
$row_rsDiscoverMethod = mysql_fetch_assoc($rsDiscoverMethod);
$totalRows_rsDiscoverMethod = mysql_num_rows($rsDiscoverMethod);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Discovery Method"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../css/membersDefault.css" rel="stylesheet"  />
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
        <div class="page users">
   <h1><i class="glyphicon glyphicon-user"></i> Update Discovery Method</h1>
   <?php if(isset($submit_error)) { ?>
   <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Method:</td>
         <td><input name="description" type="text"  value="<?php echo htmlentities($row_rsDiscoverMethod['description'], ENT_COMPAT, 'UTF-8'); ?>" size="50" maxlength="100" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Active:</td>
         <td><input <?php if (!(strcmp($row_rsDiscoverMethod['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" >Save changes</button></td>
       </tr>
     </table>
     <input type="hidden" name="ID" value="<?php echo $row_rsDiscoverMethod['ID']; ?>" />
     <input type="hidden" name="MM_update" value="form1" />
     <input type="hidden" name="ID" value="<?php echo $row_rsDiscoverMethod['ID']; ?>" />
   </form>
   </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDiscoverMethod);
?>
