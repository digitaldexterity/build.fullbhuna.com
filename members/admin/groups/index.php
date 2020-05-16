<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];
if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM usergroupsetgroup WHERE groupID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM usergroupmember WHERE groupID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$members = mysql_affected_rows();
	$delete = "DELETE FROM usergroup WHERE ID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$msg = "The group containing ".$members." was deleted.";
}

$maxRows_rsUserGroups = 100;
$pageNum_rsUserGroups = 0;
if (isset($_GET['pageNum_rsUserGroups'])) {
  $pageNum_rsUserGroups = $_GET['pageNum_rsUserGroups'];
}
$startRow_rsUserGroups = $pageNum_rsUserGroups * $maxRows_rsUserGroups;

$varRegionID_rsUserGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsUserGroups = $regionID;
}
$varGroupTypeID_rsUserGroups = "0";
if (isset($_GET['grouptypeID'])) {
  $varGroupTypeID_rsUserGroups = $_GET['grouptypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT usergroup.*, usergroup.ID AS thisgroupID,   (SELECT COUNT(usergroupmember.ID) FROM usergroupmember WHERE usergroupmember.groupID = usergroup.ID AND  usergroupmember.statusID = 1 AND (usergroupmember.expirydatetime IS NULL OR usergroupmember.expirydatetime >= NOW())) AS nummembers, usergrouptype.grouptype FROM usergroup LEFT JOIN usergrouptype ON (usergroup.grouptypeID = usergrouptype.ID) WHERE usergroup.groupsetID IS NULL AND (usergroup.regionID = 0 OR usergroup.regionID = %s OR %s = 0) AND (%s = 0 OR usergroup.grouptypeID = %s) GROUP BY usergroup.ID ORDER BY usergroup.ordernum ASC, usergroup.groupname ASC ", GetSQLValueString($varRegionID_rsUserGroups, "int"),GetSQLValueString($varRegionID_rsUserGroups, "int"),GetSQLValueString($varGroupTypeID_rsUserGroups, "int"),GetSQLValueString($varGroupTypeID_rsUserGroups, "int"));
$query_limit_rsUserGroups = sprintf("%s LIMIT %d, %d", $query_rsUserGroups, $startRow_rsUserGroups, $maxRows_rsUserGroups);
$rsUserGroups = mysql_query($query_limit_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);

if (isset($_GET['totalRows_rsUserGroups'])) {
  $totalRows_rsUserGroups = $_GET['totalRows_rsUserGroups'];
} else {
  $all_rsUserGroups = mysql_query($query_rsUserGroups);
  $totalRows_rsUserGroups = mysql_num_rows($all_rsUserGroups);
}
$totalPages_rsUserGroups = ceil($totalRows_rsUserGroups/$maxRows_rsUserGroups)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupTypes = "SELECT ID, grouptype FROM usergrouptype WHERE statusID = 1 ORDER BY grouptype ASC";
$rsGroupTypes = mysql_query($query_rsGroupTypes, $aquiescedb) or die(mysql_error());
$row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes);
$totalRows_rsGroupTypes = mysql_num_rows($rsGroupTypes);

$queryString_rsUserGroups = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUserGroups") == false && 
        stristr($param, "totalRows_rsUserGroups") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUserGroups = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUserGroups = sprintf("&totalRows_rsUserGroups=%d%s", $totalRows_rsUserGroups, $queryString_rsUserGroups);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage User Groups"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/membersDefault.css" rel="stylesheet"  />
<style><!--
<?php if($totalRows_rsGroupTypes==0) {
	echo "#filter-form { display: none; } ";
} ?>
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
                $("#info").load("/core/ajax/sort.ajax.php?table=usergroup&"+order); 
            } 
        }); 
		
    }); 
	</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page users"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-user"></i> Manage User Groups</h1>
    <ul>
      <li><strong>User Groups</strong> allow you to relate sets of users together. Any user can optionally be a member of 1 or more groups.</li><li>
Similarly <strong>User Ranks</strong> provide default access levels to site functionality. Each user has a single rank.</li></ul>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="add_group.php<?php echo isset($_GET['grouptypeID']) ? "?grouptypeID=".intval($_GET['grouptypeID']): ""; ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add User Group</a></li>
      <li><a href="group_members.php?groupID=0" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Group Members</a></li>
      <li><a href="sets/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Group Sets</a></li>
      <li><a href="types/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Group Categories</a></li>
      <li><a href="ranks/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i>  User Ranks</a></li>
      <li><a href="../relationships/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> User Relationships</a></li>
      <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Users</a></li>
    </ul></div></nav><?php require_once('../../../core/includes/alert.inc.php'); ?><form action="" method="get" id="filter-form" class="form-inline"><fieldset><legend>Filter</legend>

    <label for="grouptypeID"></label>
    <select name="grouptypeID" id="grouptypeID" onChange="this.form.submit()" class="form-control">
      <option value="0" <?php if (!(strcmp(0, @$_GET['grouptypeID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsGroupTypes['ID']?>"<?php if (!(strcmp($row_rsGroupTypes['ID'], @$_GET['grouptypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroupTypes['grouptype']?></option>
      <?php
} while ($row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes));
  $rows = mysql_num_rows($rsGroupTypes);
  if($rows > 0) {
      mysql_data_seek($rsGroupTypes, 0);
	  $row_rsGroupTypes = mysql_fetch_assoc($rsGroupTypes);
  }
?>
    </select>
 
</fieldset></form>
<?php if ($totalRows_rsUserGroups == 0) { // Show if recordset empty ?>
  <p>There are currently no User Groups in the system.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsUserGroups > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Groups <?php echo ($startRow_rsUserGroups + 1) ?> to <?php echo min($startRow_rsUserGroups + $maxRows_rsUserGroups, $totalRows_rsUserGroups) ?> of <?php echo $totalRows_rsUserGroups ?>  <span id="info">Drag and drop to re-order</span></p>
      <table  class="user-groups table table-hover">
      <thead>
        <tr><th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>Group</th>
          <th>Category</th>
        <th>Description</th><th>Cost</th>
         <th class="text-nowrap">Opt-in</th>
        <th>Members</th>
          <th colth="3">Actions</th>
      </tr></thead><tbody class="sortable">
        <?php do { ?>
          <tr  id="listItem_<?php echo $row_rsUserGroups['ID']; ?>"> <td class="handle">&nbsp;</td>
            <td class="status<?php echo $row_rsUserGroups['statusID']; ?>">&nbsp;</td>
            <td><?php echo $row_rsUserGroups['groupname']; ?></td>
            <td><?php echo $row_rsUserGroups['grouptype']; ?></td>
            <td><?php echo $row_rsUserGroups['groupdescription']; ?></td> <td class="text-right"><?php echo $row_rsUserGroups['renewalcost']>0 ? number_format($row_rsUserGroups['renewalcost'],2,".",""): "&nbsp;"; ?></td>
            <td class="optin<?php echo $row_rsUserGroups['optin']; ?>">&nbsp;</td>
           
            <td><?php echo $row_rsUserGroups['nummembers']; ?></td>
            <td><a href="group_members.php?groupID=<?php echo $row_rsUserGroups['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i> View</a> <a href="update_group.php?groupID=<?php echo $row_rsUserGroups['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="index.php?deleteID=<?php echo $row_rsUserGroups['ID']; ?>" onClick="return confirm('Are you sure you want to delete the group:\n<?php echo $row_rsUserGroups['groupname']; ?>?')" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
         </tr>
          <?php } while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups)); ?>
     </tbody></table>
    <?php } // Show if recordset not empty ?>
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsUserGroups > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUserGroups=%d%s", $currentPage, 0, $queryString_rsUserGroups); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsUserGroups > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUserGroups=%d%s", $currentPage, max(0, $pageNum_rsUserGroups - 1), $queryString_rsUserGroups); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsUserGroups < $totalPages_rsUserGroups) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUserGroups=%d%s", $currentPage, min($totalPages_rsUserGroups, $pageNum_rsUserGroups + 1), $queryString_rsUserGroups); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsUserGroups < $totalPages_rsUserGroups) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUserGroups=%d%s", $currentPage, $totalPages_rsUserGroups, $queryString_rsUserGroups); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table></div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUserGroups);

mysql_free_result($rsGroupTypes);
?>
