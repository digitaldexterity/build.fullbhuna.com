<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

if(isset($_GET['deleteID'])) { 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM productaccount WHERE ID = ".intval($_GET['deleteID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());

}

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO productaccount (groupID, approverrankID, regionID, createddatetime, createdbyID, payaccount, payother, sharedaddresses, orderforlist) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString($_POST['approverrankID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString(isset($_POST['payaccount']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['payother']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['sharedaddresses']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['orderforlist']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRank = "SELECT * FROM usertype WHERE ID>0";
$rsRank = mysql_query($query_rsRank, $aquiescedb) or die(mysql_error());
$row_rsRank = mysql_fetch_assoc($rsRank);
$totalRows_rsRank = mysql_num_rows($rsRank);

$varRegionID_rsUserGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsUserGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE (regionID = 0 OR regionID = %s) AND usergroup.statusID = 1 AND usergroup.groupsetID IS NULL ORDER BY usergroup.groupname", GetSQLValueString($varRegionID_rsUserGroups, "int"));
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

$maxRows_rsproductaccounts = 10;
$pageNum_rsproductaccounts = 0;
if (isset($_GET['pageNum_rsproductaccounts'])) {
  $pageNum_rsproductaccounts = $_GET['pageNum_rsproductaccounts'];
}
$startRow_rsproductaccounts = $pageNum_rsproductaccounts * $maxRows_rsproductaccounts;

$varRegionID_rsproductaccounts = "1";
if (isset($regionID)) {
  $varRegionID_rsproductaccounts = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsproductaccounts = sprintf("SELECT productaccount.*, usergroup.groupname, usertype.name  FROM productaccount LEFT JOIN usergroup ON (productaccount.groupID = usergroup.ID) LEFT JOIN usertype ON (productaccount.approverrankID = usertype.ID) WHERE productaccount.regionID = %s", GetSQLValueString($varRegionID_rsproductaccounts, "int"));
$query_limit_rsproductaccounts = sprintf("%s LIMIT %d, %d", $query_rsproductaccounts, $startRow_rsproductaccounts, $maxRows_rsproductaccounts);
$rsproductaccounts = mysql_query($query_limit_rsproductaccounts, $aquiescedb) or die(mysql_error());
$row_rsproductaccounts = mysql_fetch_assoc($rsproductaccounts);

if (isset($_GET['totalRows_rsproductaccounts'])) {
  $totalRows_rsproductaccounts = $_GET['totalRows_rsproductaccounts'];
} else {
  $all_rsproductaccounts = mysql_query($query_rsproductaccounts);
  $totalRows_rsproductaccounts = mysql_num_rows($all_rsproductaccounts);
}
$totalPages_rsproductaccounts = ceil($totalRows_rsproductaccounts/$maxRows_rsproductaccounts)-1;

$queryString_rsproductaccounts = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsproductaccounts") == false && 
        stristr($param, "totalRows_rsproductaccounts") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsproductaccounts = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsproductaccounts = sprintf("&totalRows_rsproductaccounts=%d%s", $totalRows_rsproductaccounts, $queryString_rsproductaccounts);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Purchase Approvals"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
    <div class="page class">
     <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
 <h1><i class="glyphicon glyphicon-shopping-cart"></i> Purchase Accounts</h1>
 <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
 <li><a href="index.php"  class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Options</a></li>
 <li><a href="../../../members/admin/groups/index.php"  class="link_users">Manage User Groups</a></li>
 <li><a href="../../../members/admin/groups/ranks/index.php"  class="link_users">Manage User Ranks</a></li>
 </ul></div></nav>
      <p>You can set up purchase groups so that they can pay by account which can require approval from another specified group member before they are processed.</p>
      <form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
        <fieldset class="form-inline"><legend>Add Approval Group</legend>
        Purchases by <select name="groupID" class="form-control">
          <option value="0">Choose group...</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsUserGroups['ID']?>"><?php echo $row_rsUserGroups['groupname']?></option>
          <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
        </select>
must be approved by anyone of rank
<select name="approverrankID" class="form-control">
  <option value="0">Choose rank...</option>
   <option value="0">Not required</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsRank['ID']?>"><?php echo $row_rsRank['name']?></option>
  <?php
} while ($row_rsRank = mysql_fetch_assoc($rsRank));
  $rows = mysql_num_rows($rsRank);
  if($rows > 0) {
      mysql_data_seek($rsRank, 0);
	  $row_rsRank = mysql_fetch_assoc($rsRank);
  }
?>
        </select> <br><label><input type="checkbox" name="sharedaddresses" value="1"> Shared address book</label> &nbsp;&nbsp; 
        <label><input type="checkbox" name="orderforlist" value="1"> Order for list</label> &nbsp;&nbsp; Can pay by:
        <label><input type="checkbox" name="payaccount" value="1"> Account</label>
        <label><input type="checkbox" name="payother" value="1" checked> Other methods</label>
        <button type="submit" class="btn btn-default btn-secondary" >Add</button>
        <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>">
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
        </fieldset>
        <input type="hidden" name="MM_insert" value="form1">
      </form>
     
      <?php if ($totalRows_rsproductaccounts == 0) { // Show if recordset empty ?>
  <p>There are no approval groups added so far.</p>
  <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsproductaccounts > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Groups <?php echo ($startRow_rsproductaccounts + 1) ?> to <?php echo min($startRow_rsproductaccounts + $maxRows_rsproductaccounts, $totalRows_rsproductaccounts) ?> of <?php echo $totalRows_rsproductaccounts ?></p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Group</th>
            <th>Approved by</th>
             <th>Shared</th>
               <th>List</th>
            <th>Account</th>
            <th>Other</th>
            <th>Delete</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsproductaccounts['statusID']; ?>">&nbsp;</td>
              <td><?php echo $row_rsproductaccounts['groupname']; ?></td>
              <td><?php echo ($row_rsproductaccounts['approverrankID']>0) ? $row_rsproductaccounts['name'] : "Not required"; ?></td>
              <td><?php echo $row_rsproductaccounts['sharedaddresses']==1 ? '<span class="glyphicon glyphicon-ok"></span>' : ''; ?></td>
                <td><?php echo $row_rsproductaccounts['orderforlist']==1 ? '<span class="glyphicon glyphicon-ok"></span>' : ''; ?></td>
              <td><?php echo $row_rsproductaccounts['payaccount']==1 ? '<span class="glyphicon glyphicon-ok"></span>' : ''; ?></td>
              <td><?php echo $row_rsproductaccounts['payother']==1 ? '<span class="glyphicon glyphicon-ok"></span>' : ''; ?></td>
              <td><a href="accounts.php?deleteID=<?php echo $row_rsproductaccounts['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to delete this purchase group?');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
            </tr>
            <?php } while ($row_rsproductaccounts = mysql_fetch_assoc($rsproductaccounts)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>

    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsRank);

mysql_free_result($rsUserGroups);

mysql_free_result($rsproductaccounts);
?>
