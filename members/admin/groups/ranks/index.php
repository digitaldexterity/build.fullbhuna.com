<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

if(isset($_GET['deleterankID'])) {
	if(intval($_GET['deleterankID'])>1 && intval($_GET['deleterankID'])< 8 && intval($_GET['deleterankID'])<$_SESSION['MM_UserGroup']) { 
		mysql_select_db($database_aquiescedb, $aquiescedb);

		$delete = "DELETE usertype FROM usertype LEFT JOIN users ON (users.usertypeID = usertype.ID)  WHERE users.ID IS NULL AND usertype.ID = ".intval($_GET['deleterankID']);
		mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	}
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT usertype.*, COUNT(users.ID) AS number FROM usertype LEFT JOIN users ON (users.usertypeID = usertype.ID) GROUP BY usertype.ID ORDER BY usertype.ID DESC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "User Ranks"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
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
    <h1><i class="glyphicon glyphicon-user"></i> User Ranks </h1>
    <p>Every user has a user rank between -2 and 10 which determines what areas of the site they have access to. <br />
    Higher user ranks have greater access.</p>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="add_user_type.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add a user rank</a></li>
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> User Groups</a></li>
    </ul></div></nav>
    <table  class="table table-hover">
    <thead>
      <tr>
        <th>Rank</th>
        <th>Name</th>
        <th>Count</th>
        <th colspan="2">Actions</th>
      </tr></thead><tbody>
      <?php do { ?>
        <tr>
          <td><?php echo $row_rsUserTypes['ID']; ?></td>
          <td><?php echo $row_rsUserTypes['name']; ?></td>
          <td><a href="../../index.php?usertypeID=<?php echo $row_rsUserTypes['ID']; ?>"><?php echo $row_rsUserTypes['number']; ?></a></td>
          <td><?php if($row_rsUserTypes['ID']<=$_SESSION['MM_UserGroup'] ) { ?><a href="update_user_type.php?usertypeID=<?php echo $row_rsUserTypes['ID']; ?>" class="link_edit icon_only">Edit</a><?php } ?></td>
          <td><?php if($row_rsUserTypes['number'] == 0 && $row_rsUserTypes['ID']>1 && $row_rsUserTypes['ID']< 8 && $row_rsUserTypes['ID']<$_SESSION['MM_UserGroup']) { ?><a href="index.php?deleterankID=<?php echo $row_rsUserTypes['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to delete this Rank?')"><i class="glyphicon glyphicon-trash"></i> Delete</a><?php } ?></td>
        </tr>
        <?php } while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes)); ?></tbody>
    </table>
    <h2>Default Rank Access</h2>
    <table class="form-table">
      <tr>
        <th>Rank</th>
        <th>Site</th>
        <th>Control Panel</th>
        </tr>
      <tr>
        <th>&lt; 1 Non-members</th>
        <td>Access to public pages</td>
        <td>No access</td>
        </tr>
      <tr>
        <th>1 Member</th>
        <td>Access to public and member pages</td>
        <td>No access</td>
        </tr>
      <tr>
        <th>6 Agent</th>
        <td>Access to public member pages. Limiited access to Control Panel</td>
        <td>Limited access: can update specific categories with granted access</td>
        </tr>
      <tr>
        <th>7 Staff Member</th>
        <td>Access to public member pages. Limiited access to Control Panel</td>
        <td>Limited access: can only update products</td>
        </tr>
      <tr>
        <th>8 Editor</th>
        <td>Acess to public member pages. Limited access to Control Panel for designated site</td>
        <td>Access all areas of designated site</td>
        </tr>
      <tr>
        <th>9 Manager</th>
        <td>Acess to public member pages. Full access to Control Panel for all sites</td>
        <td>Access all areas of all sites (if multiple)</td>
        </tr>
    </table></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUserTypes);
?>