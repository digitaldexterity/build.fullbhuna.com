<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../includes/userfunctions.inc.php'); ?>
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

if(isset($_GET['deletegroupID']) && intval($_GET['deletegroupID'])>0) {
	  mysql_select_db($database_aquiescedb, $aquiescedb);
	  $delete = "DELETE FROM usergroupsetgroup WHERE groupsetID = ".intval($_GET['groupsetID'])." AND groupID = ".intval($_GET['deletegroupID']);																						    mysql_query($delete, $aquiescedb) or die(mysql_error());																																																																					
	
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "addform")) {
  $insertSQL = sprintf("INSERT INTO usergroupsetgroup (groupsetID, groupID, relationship, createdbyID, createddatetime) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['groupsetID'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString($_POST['relationship'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "groupsetform")) {
  $updateSQL = sprintf("UPDATE usergroupset SET groupsetname=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['groupsetname'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "groupsetform")) {
	buildGroupSet($_POST['ID'],$_POST['modifiedbyID']);
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

$varGroupSetID_rsGroups = "-1";
if (isset($_GET['groupsetID'])) {
  $varGroupSetID_rsGroups = $_GET['groupsetID'];
}
$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT usergroup.ID, groupname FROM usergroup LEFT JOIN usergroupsetgroup ON (usergroup.ID = usergroupsetgroup.groupID) WHERE usergroup.statusID = 1 AND  (usergroup.regionID = 0 OR usergroup.regionID = %s) AND (usergroupsetgroup.groupsetID  IS NULL OR usergroupsetgroup.groupsetID != %s) ORDER BY groupname ASC", GetSQLValueString($varRegionID_rsGroups, "int"),GetSQLValueString($varGroupSetID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$colname_rsSetMembers = "-1";
if (isset($_GET['groupsetID'])) {
  $colname_rsSetMembers = $_GET['groupsetID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSetMembers = sprintf("SELECT usergroupsetgroup.groupID, usergroup.groupname, COUNT(usergroupmember.ID) AS members, usergroupsetgroup.relationship FROM usergroupsetgroup LEFT JOIN usergroup ON (usergroup.ID = usergroupsetgroup.groupID) LEFT JOIN usergroupmember ON (usergroup.ID = usergroupmember.groupID) WHERE usergroupsetgroup.groupsetID = %s GROUP BY usergroupsetgroup.ID ORDER BY usergroup.groupname", GetSQLValueString($colname_rsSetMembers, "int"));
$rsSetMembers = mysql_query($query_rsSetMembers, $aquiescedb) or die(mysql_error());
$row_rsSetMembers = mysql_fetch_assoc($rsSetMembers);
$totalRows_rsSetMembers = mysql_num_rows($rsSetMembers);

$colname_rsThisGroupSet = "-1";
if (isset($_GET['groupsetID'])) {
  $colname_rsThisGroupSet = $_GET['groupsetID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGroupSet = sprintf("SELECT * FROM usergroupset WHERE ID = %s", GetSQLValueString($colname_rsThisGroupSet, "int"));
$rsThisGroupSet = mysql_query($query_rsThisGroupSet, $aquiescedb) or die(mysql_error());
$row_rsThisGroupSet = mysql_fetch_assoc($rsThisGroupSet);
$totalRows_rsThisGroupSet = mysql_num_rows($rsThisGroupSet);

$varRegionID_rsAllActiveUsers = "1";
if (isset($regionID)) {
  $varRegionID_rsAllActiveUsers = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllActiveUsers = sprintf("SELECT COUNT(ID) AS totalusers FROM users WHERE usertypeID >=0 AND (users.regionID = 0 OR users.regionID = %s)", GetSQLValueString($varRegionID_rsAllActiveUsers, "int"));
$rsAllActiveUsers = mysql_query($query_rsAllActiveUsers, $aquiescedb) or die(mysql_error());
$row_rsAllActiveUsers = mysql_fetch_assoc($rsAllActiveUsers);
$totalRows_rsAllActiveUsers = mysql_num_rows($rsAllActiveUsers);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update User Group Set"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../../SpryAssets/SpryValidationSelect.js"></script>
<script>
addListener("load",init);
function init() {
	addListener("click",submitForm,document.getElementById('submitbutton'));
}

function submitForm() {
	document.getElementById('groupsetform').submit();
}
</script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="css/groupsets.css" rel="stylesheet"  />
<link href="../../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
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
    <h1><i class="glyphicon glyphicon-user"></i> Update User Group Set</h1>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="groupsetform" id="groupsetform" clas="form-inline">
      <span id="sprytextfield1">
      <label>
        <input name="groupsetname" type="text" id="groupsetname" value="<?php echo $row_rsThisGroupSet['groupsetname']; ?>" size="50" maxlength="50" class="form-control" />
      </label>
      <span class="textfieldRequiredMsg">A name is required.</span></span><input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsThisGroupSet['ID']; ?>" />
      <input type="hidden" name="MM_update" value="groupsetform" />
    </form>
    <form action="<?php echo $editFormAction; ?>" method="POST" name="addform" id="addform" class="form-inline">
      <label>
        <select name="relationship" id="relationship" class="form-control" >
          <option value="1">Include</option>
          <option value="-1">Exclude</option>
        </select>
      </label>
      <span id="spryselect1">
      <label>
        <select name="groupID" id="groupID" class="form-control" >
          <option value="">Choose group...</option>
          <option value="0">All users</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsGroups['ID']?>"><?php echo $row_rsGroups['groupname']?></option>
          <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
        </select>
      </label>
      <span class="selectRequiredMsg">Please select a group.</span></span>
     
        <button type="submit" name="addbutton" id="addbutton" class="btn btn-default btn-secondary" onClick="if(document.getElementById('groupID').value==0 && document.getElementById('relationship').value==-1) { alert('Sorry, you can not exlude all active users'); return false; }" >Add...</button>
     
      <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="groupsetID" type="hidden" id="groupsetID" value="<?php echo $row_rsThisGroupSet['ID']; ?>" />
      <input type="hidden" name="MM_insert" value="addform" />
    </form>
    <?php if ($totalRows_rsSetMembers == 0) { // Show if recordset empty ?>
  <p>There are currently no groups added to this set.</p>
  <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsSetMembers > 0) { // Show if recordset not empty ?>
    <table class="table table-hover">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th>Group</th>
        <th>Members</th>
        <th colspan="2">Actions</th>
        </tr></thead><tbody>
      <?php do { ?>
        <tr>
          <td class="relationship<?php echo $row_rsSetMembers['relationship']; ?>"><?php echo $row_rsSetMembers['relationship']; ?></td>
          <td><?php echo ($row_rsSetMembers['groupID'] > 0) ?  $row_rsSetMembers['groupname'] : "All active users"; ?></td>
          <td><?php $total = ($row_rsSetMembers['groupID'] > 0) ?  $row_rsSetMembers['members'] : $row_rsAllActiveUsers['totalusers']; echo $total;  ?></td>
          <td><a href="update_group_set.php?deletegroupID=<?php echo $row_rsSetMembers['groupID']; ?>&groupsetID=<?php echo $row_rsThisGroupSet['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to delete this group from the set?');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
          <td><a href="../group_members.php?groupID=<?php echo $row_rsSetMembers['groupID']; ?>" class="link_view">View</a></td>
        </tr>
        
        <?php } while ($row_rsSetMembers = mysql_fetch_assoc($rsSetMembers)); ?>
        </tbody>
    </table>
    <?php } // Show if recordset not empty ?>
<p>
      <button type="submit" name="submitbutton" id="submitbutton" class="btn btn-primary" >Save changes...</button>
    </p>
    <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
//-->
    </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGroups);

mysql_free_result($rsSetMembers);

mysql_free_result($rsThisGroupSet);

mysql_free_result($rsAllActiveUsers);
?>
