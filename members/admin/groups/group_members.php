<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/userfunctions.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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



if(isset($_POST['formaction']) && $_POST['formaction']>0 && isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { // is action and selected items
mysql_select_db($database_aquiescedb, $aquiescedb);
	if($_REQUEST['formaction']==1) { // delete
		foreach($_SESSION['checkbox'] as $key=> $value) {
			$delete = "DELETE FROM usergroupmember WHERE userID = ".intval($value)." AND groupID = ".intval($_REQUEST['groupID']); 
			mysql_query($delete, $aquiescedb) or die(mysql_error()); 		
		}
		
		$msg = "Users successfully removed from group";unset($_SESSION['checkbox']);
 	} else if($_REQUEST['formaction']==2 && isset($_POST['addtogroupID']) && $_POST['addtogroupID']>0 && $_POST['addtogroupID'] != $_POST['groupID']) { // add to group
	
		foreach($_SESSION['checkbox'] as $key=> $userID) {
			$groupID = intval($_REQUEST['addtogroupID']);
			addUsertoGroup($userID, $groupID,$_POST['modifiedbyID']);
			
 		}
		
 	$msg = "Users successfully added to group";unset($_SESSION['checkbox']);
 	} else if($_REQUEST['formaction']==3) { // opt in
		foreach($_SESSION['checkbox'] as $key=> $userID) {
			$update = "UPDATE users SET emailoptin = 1 WHERE ID = ".intval($userID);
			mysql_query($update, $aquiescedb) or die(mysql_error());
			
 		}
 	$msg = "Users successfully opted-in";unset($_SESSION['checkbox']);
	header("location: group_members.php?groupID=".intval($_REQUEST['groupID'])."&msg=".urlencode($msg)); exit;
 	} else if($_POST['formaction']==4) { // generate logins
		if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { 
			$send_emails = (count($_SESSION['checkbox'])<=50) ? true : false;// for small numbers we can email

			foreach($_SESSION['checkbox'] as $key=>$userID) {
				setUsernamePassword($userID,"","",$send_emails, true);
				
			}// end foreach
		$msg = count($_SESSION['checkbox'])." selected users have been given new usernames and passwords.";
		$msg .= $send_emails ? "\n\nThe users with email addresses have been sent their login details." : "";
		}
	}
} // is action

$sortby = isset($_GET['sortby']) ? preg_replace("/[^a-zA-Z0-9\s\.]+/", "", $_GET['sortby']) : "usergroupmember.ordernum ASC, surname ASC";

$groupby = (isset($_REQUEST['groupID']) && $_REQUEST['groupID'] > 0 ) ? " GROUP BY usergroupmember.ID " : " GROUP BY users.ID ";

$where = (isset($_REQUEST['groupID']) && $_REQUEST['groupID'] == -2) ? "" : " AND (users.regionID IS NULL OR users.regionID = 0 OR users.regionID = ".$regionID.") ";

$maxrows = (isset($_REQUEST['csv']) && $_REQUEST['csv'] == 1) ? 10000 : 300;

$currentPage = $_SERVER["PHP_SELF"];

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisGroup = "SELECT ID, groupname FROM usergroup WHERE ID = ".intval($_REQUEST['groupID']);
$rsThisGroup = mysql_query($query_rsThisGroup, $aquiescedb) or die(mysql_error());
$row_rsThisGroup = mysql_fetch_assoc($rsThisGroup);
$totalRows_rsThisGroup = mysql_num_rows($rsThisGroup);


$maxRows_rsGroupMembers = $maxrows;
$pageNum_rsGroupMembers = 0;
if (isset($_GET['pageNum_rsGroupMembers'])) {
  $pageNum_rsGroupMembers = $_GET['pageNum_rsGroupMembers'];
}
$startRow_rsGroupMembers = $pageNum_rsGroupMembers * $maxRows_rsGroupMembers;

$varGroupID_rsGroupMembers = "-1";
if (isset($_REQUEST['groupID'])) {
  $varGroupID_rsGroupMembers = $_REQUEST['groupID'];
}
$varSearch_rsGroupMembers = "%";
if (isset($_GET['search'])) {
  $varSearch_rsGroupMembers = $_GET['search'];
}
$varShowExpired_rsGroupMembers = "0";
if (isset($_GET['showexpired'])) {
  $varShowExpired_rsGroupMembers = $_GET['showexpired'];
}
$varUserType_rsGroupMembers = "0";
if (isset($_GET['usertypeID'])) {
  $varUserType_rsGroupMembers = $_GET['usertypeID'];
}
$varOptIn_rsGroupMembers = "0";
if (isset($_REQUEST['showoptin'])) {
  $varOptIn_rsGroupMembers = $_REQUEST['showoptin'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupMembers = sprintf("SELECT usergroupmember.ID,usergroupmember.statusID,usergroupmember.expirydatetime, users.ID AS userID, users.imageURL, users.jobtitle,users.firstname, users.surname,users.dateadded, users.gender, users.telephone, users.mobile, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, location.telephone2, location.telephone3, users.email, users.emailoptin,users.emailbounced, usertype.name AS usertype ,GROUP_CONCAT(usergroup.groupname) as groups, COUNT(usergroup.ID) AS number , users.username, users.plainpassword, users.usertypeID FROM users LEFT JOIN usergroupmember ON (usergroupmember.userID = users.ID) LEFT JOIN location ON (location.ID = users.defaultaddressID) LEFT JOIN usertype ON (users.usertypeID = usertype.ID) LEFT JOIN usergroup ON (usergroupmember.groupID = usergroup.ID) WHERE (%s = 1 OR usergroupmember.expirydatetime IS NULL OR usergroupmember.expirydatetime >= NOW()) AND (%s <0 OR usergroupmember.groupID = %s OR (%s = 0 AND usergroupmember.groupID IS NOT NULL)) AND (%s = 0 OR (users.emailoptin = 1 AND users.email !='')) AND (%s = 0 OR %s = users.usertypeID) AND (users.surname LIKE %s OR users.email LIKE %s) ".$where.$groupby." ORDER BY ".$sortby."", GetSQLValueString($varShowExpired_rsGroupMembers, "int"),GetSQLValueString($varGroupID_rsGroupMembers, "int"),GetSQLValueString($varGroupID_rsGroupMembers, "int"),GetSQLValueString($varGroupID_rsGroupMembers, "int"),GetSQLValueString($varOptIn_rsGroupMembers, "int"),GetSQLValueString($varUserType_rsGroupMembers, "int"),GetSQLValueString($varUserType_rsGroupMembers, "int"),GetSQLValueString($varSearch_rsGroupMembers . "%", "text"),GetSQLValueString("%" . $varSearch_rsGroupMembers . "%", "text"));
$query_limit_rsGroupMembers = sprintf("%s LIMIT %d, %d", $query_rsGroupMembers, $startRow_rsGroupMembers, $maxRows_rsGroupMembers);
$rsGroupMembers = mysql_query($query_limit_rsGroupMembers, $aquiescedb) or die(mysql_error());
$row_rsGroupMembers = mysql_fetch_assoc($rsGroupMembers);

if (isset($_GET['totalRows_rsGroupMembers'])) {
  $totalRows_rsGroupMembers = $_GET['totalRows_rsGroupMembers'];
} else {
  $all_rsGroupMembers = mysql_query($query_rsGroupMembers);
  $totalRows_rsGroupMembers = mysql_num_rows($all_rsGroupMembers);
}
$totalPages_rsGroupMembers = ceil($totalRows_rsGroupMembers/$maxRows_rsGroupMembers)-1;

$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE usergroup.regionID = 0 OR usergroup.regionID = %s ORDER BY groupname ASC", GetSQLValueString($varRegionID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype WHERE ID >= 1 ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);

$queryString_rsGroupMembers = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGroupMembers") == false && 
        stristr($param, "totalRows_rsGroupMembers") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGroupMembers = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGroupMembers = sprintf("&totalRows_rsGroupMembers=%d%s", $totalRows_rsGroupMembers, $queryString_rsGroupMembers);

?><?php if(isset($_GET['csv']) && $_GET['csv'] == 1) {
header ('Content-disposition: attachment; filename=group-members-'.date('d-m-Y').'.csv;'); 
header("Content-type: application/octet-stream");
 print "FIRST NAME,SURNAME,ROLE,EMAIL,ADDRESS 1,ADDRESS 2,ADDRESS 3,ADDRESS 4,ADDRESS 5,POSTCODE,TELEPHONE 1,TELEPHONE 2,TELEPHONE 3, OPT-IN, USERTYPE, GENDER, DATE ADDED, EXPIRY, GROUPS, USERNAME, PASSWORD\n";

 if ($totalRows_rsGroupMembers > 0) { // Show if recordset not empty 
 do { 

	  print "\"".$row_rsGroupMembers['firstname']."\",";
	   print "\"".$row_rsGroupMembers['surname']."\",";
	    print "\"".$row_rsGroupMembers['jobtitle']."\",";
	    print "\"".$row_rsGroupMembers['email']."\",";
		 print "\"".$row_rsGroupMembers['address1']."\",";
		  print "\"".$row_rsGroupMembers['address2']."\",";
		   print "\"".$row_rsGroupMembers['address3']."\",";
		    print "\"".$row_rsGroupMembers['address4']."\",";
			 print "\"".$row_rsGroupMembers['address5']."\",";
			  print "\"".$row_rsGroupMembers['postcode']."\",";
			   print "\"".$row_rsGroupMembers['telephone']."\",";
			    print "\"".$row_rsGroupMembers['mobile']."\",";
				 print "\"".$row_rsGroupMembers['telephone1']."\",";
				 print "\"".$row_rsGroupMembers['emailoptin']."\",";
				  print "\"".$row_rsGroupMembers['usertype']."\",";
				  print "\"".$row_rsGroupMembers['gender']."\",";
				   print "\"".date('d M Y', strtotime($row_rsGroupMembers['dateadded']))."\",";
				  print "\""; print isset($row_rsGroupMembers['expirydatetime']) ? date('d M Y', strtotime($row_rsGroupMembers['expirydatetime'])) : " "; print "\",";
				  print "\"".$row_rsGroupMembers['groups']."\",";
				   print "\"".$row_rsGroupMembers['username']."\",";
				   $password = (isset($row_rsGroupMembers['plainpassword']) && $row_rsGroupMembers['usertypeID'] < $_SESSION['MM_UserGroup']) ? $row_rsGroupMembers['plainpassword'] : "N/A (encrypted)";
				   print "\"".$password."\"\n";
	 
     
	
         
      
		 } while ($row_rsGroupMembers = mysql_fetch_assoc($rsGroupMembers));   
		
       } // Show if recordset not empty 
 exit;
}// end csv

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "User Group Members: ".$row_rsThisGroup['groupname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="/core/scripts/checkbox/checkboxes.js"></script><?php require_once('../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<link href="../../css/membersDefault.css" rel="stylesheet"  />
<style><!--
<?php if(isset($_GET['groupID']) && $_GET['groupID']>0)  { 
	echo ".multi { display: none !important; }";
} else {
	echo ".single { display: none !important; }";
}
?>
--></style>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=usergroupmember&"+order); 
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
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page users">
         <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
 <h1><i class="glyphicon glyphicon-user"></i> Group Members:
   <?php $groupID = (isset($_REQUEST['groupID']) && $_REQUEST['groupID'] !="") ? $_REQUEST['groupID'] : 0;
 switch($groupID) {
		case -1 : echo "All users"; break;
		case 0 : echo "All members"; break;
		default  : echo $row_rsThisGroup['groupname']; } ?></h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link" ><i class="glyphicon glyphicon-arrow-left"></i> Manage Groups</a></li>
    </ul></div></nav><form action="group_members.php" method="get" id="formfilter"><fieldset class="form-inline"><legend>Filter</legend>
    <div class="input-group"><input name="search" placeholder="Search by surname or email" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search'],ENT_COMPAT, "UTF-8") : ""; ?>" size="30" maxlength="30" class="form-control">
        <button type="submit" class="btn btn-default btn-secondary" >Search</button></div>
      <select class="form-control" name="groupID" id="groupID" onChange="document.getElementById('csv').value = 0; this.form.submit();">
        <option value="-2" <?php if (!(strcmp(-2, htmlentities($groupID)))) {echo "selected=\"selected\"";} ?>>All users (all sites)</option>
        <option value="-1" <?php if (!(strcmp(-1, htmlentities($groupID)))) {echo "selected=\"selected\"";} ?>>All users (this site)</option>
        <option value="0" <?php if (!(strcmp(0, htmlentities($groupID)))) {echo "selected=\"selected\"";} ?>>All group members</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], htmlentities($groupID)))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
        <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
    </select>
      <select class="form-control" name="usertypeID" id="usertypeID" onChange="document.getElementById('csv').value = 0; this.form.submit();">
      <option value="0"<?php if (!isset($_GET['usertypeID']) ||  $_GET['usertypeID']==0) {echo "selected=\"selected\"";} ?>>All Ranks</option>
       <option value="-2"<?php if ( @$_GET['usertypeID']==-2) {echo "selected=\"selected\"";} ?>>Banned</option>
       <option value="-1"<?php if ( @$_GET['usertypeID']==-1) {echo "selected=\"selected\"";} ?>>Non-users</option>
        <?php
do {  
?>
        <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], @$_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
        <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
      </select>
      <select class="form-control" name="sortby" id="sortby" onChange="document.getElementById('csv').value = 0; this.form.submit();">
      <option value="usergroupmember.ordernum ASC" <?php if (!isset($_GET['sortby']) || $_GET['sortby'] == "usergroupmember.ordernum ASC") {echo "selected=\"selected\"";} ?>>Sort by drag and drop order</option>
        <option value="surname ASC" <?php if (isset($_GET['sortby']) && $_GET['sortby'] == "surname ASC") {echo "selected=\"selected\"";} ?>>Sort alphabetically</option>
        <option value="dateadded DESC" <?php if (isset($_GET['sortby']) && $_GET['sortby'] == "dateadded DESC") {echo "selected=\"selected\"";} ?>>Sort by date</option>
    </select><input type="hidden" name="csv" id="csv" value="0" /><label> &nbsp;&nbsp;&nbsp;
<input type="checkbox" name="showoptin" id="showoptin" onClick="document.getElementById('csv').value = 0; this.form.submit();" <?php if(isset($_REQUEST['showoptin'])) { echo "checked=\"checked\""; } ?> value="1" />
      Show only email opt-ins</label> &nbsp;&nbsp;&nbsp;
      
      <label>
        <input type="checkbox" name="showexpired" id="showexpired" onClick="document.getElementById('csv').value = 0; this.form.submit();" <?php if(isset($_REQUEST['showexpired'])) { echo "checked=\"checked\""; } ?> value="1">
        Show expired</label>
    </fieldset>
    </form>
    <?php require_once('../../../core/includes/alert.inc.php'); ?>
<?php if ($totalRows_rsGroupMembers == 0) { // Show if recordset empty ?>
      <p>There are no members in this group. <?php echo $query_rsGroupMembers; ?></p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsGroupMembers > 0) { // Show if recordset not empty ?>
   
    <form action="group_members.php" method="post" name="form1" id="form1">
      <p>Group members <?php echo ($startRow_rsGroupMembers + 1) ?> to <?php echo min($startRow_rsGroupMembers + $maxRows_rsGroupMembers, $totalRows_rsGroupMembers) ?> of <?php echo $totalRows_rsGroupMembers ?> (<span id="checkedCount"></span> selected). <span id="info">Drag and drop re-order</span> <a href="javascript:void(0);" onClick="document.getElementById('csv').value = 1; document.getElementById('formfilter').submit();" class="link_csv icon_with_text">Download all results as spreadsheet</a></p>
      <table class="table table-hover">
      <thead><tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th>  <th>&nbsp;</th> <th>&nbsp;</th>
          <th>Name</th>
          <th>Email</th><th>Optin</th><th>GDPR</th><th>Bounce</th>
          <th>Rank</th>
          <th class="multi">Groups</th>
          <th class="single">Added</th>
          <th class="single">Expires</th>
          <th>Edit</th>
        </tr></thead><tbody>
        <tr><td>&nbsp;</td><td>&nbsp;</td>
          <td> <input type="checkbox" name="checkAll" id="checkAll" onClick="checkUncheckAll(this);" /></td>
          
          <td><em>Select all</em></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>  
            <td>&nbsp;</td>
              <td>&nbsp;</td>
         <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr></tbody><tbody class="sortable">
        <?php do { ?>
          <tr id="listItem_<?php echo $row_rsGroupMembers['ID']; ?>" ><td class= "handle" title="Drag and drop order">&nbsp;</td> <td class="text-nowrap status<?php echo ($groupID <1 || ($row_rsGroupMembers['statusID']==1 && (!isset($row_rsGroupMembers['expirydatetime']) || $row_rsGroupMembers['expirydatetime'] >= date('Y-m-d H:i:s')))) ? 1 : 2; ?>">&nbsp;</td>
            <td class="text-nowrap">
              <input type="checkbox" name="user[<?php echo $row_rsGroupMembers['userID']; ?>]" id="user<?php echo $row_rsGroupMembers['userID']; ?>" value="<?php echo $row_rsGroupMembers['userID']; ?>" />
            </td>
            
            <td><a href="../modify_user.php?userID=<?php echo $row_rsGroupMembers['userID']; ?>"  class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsGroupMembers['imageURL'],"thumb"); ?>)"></a></td>
            
           
            <td class="text-nowrap"><a href="../modify_user.php?userID=<?php echo $row_rsGroupMembers['userID']; ?>"><?php echo $row_rsGroupMembers['firstname']; ?> <?php echo $row_rsGroupMembers['surname']; ?></a><?php echo isset($row_rsGroupMembers['jobtitle']) ? "<br><em>".$row_rsGroupMembers['jobtitle']."</em>" : ""; ?></td>
            <td class="text-nowrap"><a href="mailto:<?php echo $row_rsGroupMembers['email']; ?>"><?php echo $row_rsGroupMembers['email']; ?></a></td>
            <td class="text-nowrap"><?php if($row_rsGroupMembers['emailoptin']==1) { ?>&nbsp;<i class="glyphicon glyphicon-ok"></i><?php } ?>&nbsp;</td>
            <td class="text-nowrap"><?php if(isset($row_rsGroupMembers['gdpr_date'])) { ?>&nbsp;<i class="glyphicon glyphicon-ok"></i><?php } ?>&nbsp;</td>
            <td class="text-nowrap"><?php if($row_rsGroupMembers['emailbounced']==1) { ?>&nbsp;<img src="../../../core/images/icons/flag_red.png" alt="Opting in to group emails" width="16" height="16" style="vertical-align:
middle;" /><?php } ?>&nbsp;</td>
            <td><?php echo $row_rsGroupMembers['usertype']; ?></td>
            <td class="multi"><abbr title="<?php echo $row_rsGroupMembers['groups']; ?>"><?php echo $row_rsGroupMembers['number']; ?></abbr></td>
            <td class="text-nowrap single"><?php echo date('d M Y', strtotime($row_rsGroupMembers['dateadded'])); ?></td>
            <td class="text-nowrap single"><?php echo isset($row_rsGroupMembers['expirydatetime']) ? date('d M Y', strtotime($row_rsGroupMembers['expirydatetime'])) : "&nbsp;"; ?></td>
            <td><a href="../modify_user.php?userID=<?php echo $row_rsGroupMembers['userID']; ?>&amp;defaultTab=3" class="link_edit icon_only">Edit</a></td>
        </tr>
          <?php } while ($row_rsGroupMembers = mysql_fetch_assoc($rsGroupMembers)); ?>
     </tbody></table>
      <p>With selected: <?php if(isset($_REQUEST['groupID']) && $_REQUEST['groupID']>0) { ?><a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to remove these members from the group?')) { document.getElementById('formaction').value=1; document.getElementById('form1').submit(); } return false;">Remove from this group</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to make all members of this group opt in to emails?')) { document.getElementById('formaction').value=3; document.getElementById('form1').submit(); } return false;">Opt in</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to generate logins for all these users?\n\nIf fewer than 50 chosen, each user will be automatically emailed their login credentials.')) { document.getElementById('formaction').value=4; document.getElementById('form1').submit(); } return false;">Generate Logins</a>&nbsp;&nbsp;|&nbsp;&nbsp;<?php } ?><a href="javascript:void(0);" onClick="if(document.getElementById('addtogroupID').value>0) { document.getElementById('formaction').value=2; document.getElementById('form1').submit(); } else { alert('Please choose a group to add to.');} return false; ">Add to group</a> :
        <select name="addtogroupID" id="addtogroupID" >
          <option value="0" <?php if (!(strcmp(0, htmlentities(@$_REQUEST['addtogroupID'])))) {echo "selected=\"selected\"";} ?>>Choose group...</option>
          <?php
do {  if($row_rsGroups['ID'] != $_REQUEST['groupID']) {
?>
          <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], htmlentities(@$_REQUEST['addtogroupID'])))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
          <?php }
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
        </select>
        <input name="groupID" type="hidden" id="groupID" value="<?php echo htmlentities($_REQUEST['groupID']); ?>" />
        <input name="formaction" type="hidden" id="formaction" value="0" />
       
          <input type="hidden" name="optin" id="optin" value="<?php echo (isset($_REQUEST['optin']) && $_REQUEST['optin'] == 1) ? 1 : 0; ?>" />
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      </p>
      <p><label>
Link to allow members to add themselves: 
                  <input name="grouplink" type="text" id="grouplink" value="<?php echo getProtocol()."://".$_SERVER['HTTP_HOST']."/members/index.php?groupID=".$row_rsThisGroup['ID']."&groupkey=".md5($row_rsThisGroup['ID'].PRIVATE_KEY); ?>" size="50" maxlength="50">
        </label>
      </p>
      <table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsGroupMembers > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsGroupMembers=%d%s", $currentPage, 0, $queryString_rsGroupMembers); ?>">First</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsGroupMembers > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsGroupMembers=%d%s", $currentPage, max(0, $pageNum_rsGroupMembers - 1), $queryString_rsGroupMembers); ?>" rel="prev">Previous</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsGroupMembers < $totalPages_rsGroupMembers) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsGroupMembers=%d%s", $currentPage, min($totalPages_rsGroupMembers, $pageNum_rsGroupMembers + 1), $queryString_rsGroupMembers); ?>" rel="next">Next</a>
              <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsGroupMembers < $totalPages_rsGroupMembers) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsGroupMembers=%d%s", $currentPage, $totalPages_rsGroupMembers, $queryString_rsGroupMembers); ?>">Last</a>
              <?php } // Show if not last page ?></td>
        </tr>
      </table></form>
      <?php } // Show if recordset not empty ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisGroup);

mysql_free_result($rsGroupMembers);

mysql_free_result($rsGroups);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUserTypes);
?>
