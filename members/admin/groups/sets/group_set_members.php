<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../../core/includes/framework.inc.php'); ?>
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
	$select = "SELECT groupID, relationship FROM usergroupsetgroup WHERE groupsetID = ".intval($_GET['groupsetID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
  if(mysql_num_rows($result)>0) { // is groups
  $url = $_SERVER['REQUEST_URI'];
  $url = removeQueryVarFromURL($url,"start");
  $url = removeQueryVarFromURL($url,"total");
	$rank = isset($_GET['usertypeID']) ? intval($_GET['usertypeID']) : 0;
	$include = "";
	$exclude = "";
	$count = 50;
	$start = isset($_GET['start']) ? $_GET['start'] : 0;
	$limit = " LIMIT ".$start.", ".$count."";
	$i = 1;
  while($row = mysql_fetch_assoc($result)) { // count through groups
 
  if($row['relationship'] == 1) {
	  $include .= ($include == "") ? "\n" : "\nOR\n ";
	
	  $include .= ($row['groupID'] >0) ? "(users.ID = usergroupmember.userID AND usergroupmember.groupID = ".$row['groupID'].") " : "users.usertypeID >=0 ";
	  
  } else if($row['relationship'] == -1) {
	  $exclude .= "\nAND NOT EXISTS (SELECT users".$i.".ID FROM users AS users".$i.", usergroupmember AS usergroupmember".$i." WHERE users.ID = users".$i.".ID AND users".$i.".ID = usergroupmember".$i.".userID AND usergroupmember".$i.".groupID = ".$row['groupID'].") ";
	  $i++;
  }									
	
	
  } // end count through groups
  if($include != "") { // is include portion
  $userselect = "SELECT DISTINCT users.ID, users.firstname, users.surname, users.email, users.emailoptin
	FROM users, usergroupmember
	WHERE usertypeID >= ".$rank."";
	$userselect .= isset($_GET['optin']) ? " AND email !='' AND emailoptin = 1 " : "";	
	$userselect .= " AND (".$include.") ".$exclude;
	 
	$allusers = mysql_query($userselect, $aquiescedb) or die(mysql_error()." ". $userselect);
	if(isset($_GET['total'])) {
		$total = intval($_GET['total']);
	} else {
		$total = mysql_num_rows($allusers);
	}
	$limitusers = mysql_query($userselect.$limit, $aquiescedb) or die(mysql_error()." ". $userselect);
	$limittotal = mysql_num_rows($limitusers);
  } // end is include portion
  } // is groups
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

$colname_rsThisSet = "-1";
if (isset($_GET['groupsetID'])) {
  $colname_rsThisSet = $_GET['groupsetID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSet = sprintf("SELECT groupsetname FROM usergroupset WHERE ID = %s", GetSQLValueString($colname_rsThisSet, "int"));
$rsThisSet = mysql_query($query_rsThisSet, $aquiescedb) or die(mysql_error());
$row_rsThisSet = mysql_fetch_assoc($rsThisSet);
$totalRows_rsThisSet = mysql_num_rows($rsThisSet);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype WHERE ID >= 1 ORDER BY ID ASC";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

if(isset($_GET['csv']) && $_GET['csv'] == 1) {
	$headers="";
	exportCSV($headers, $allusers, $filename="Users-YY-MM-DD");
	exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Group Set Members: ".$row_rsThisSet['groupsetname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
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
    <h1><i class="glyphicon glyphicon-user"></i> Group Set Members: <?php echo $row_rsThisSet['groupsetname']; ?></h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></li>
</ul></div></nav>
    	<form action="" method="get" name="form1" id="form1">
    	  <fieldset class="form-inline">
  	        <legend>Filter</legend>
    	    
    	      Rank: 
    	      <select name="usertypeID" id="usertypeID" class="form-control">
    	        <option value="0" <?php if (!(strcmp(0, @$_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>>All active users</option>
    	        <?php
do {  
?>
    	        <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'],@$_GET['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['name']?></option>
    	        <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
              </select>
    	    or higher 
    	    <label><input <?php if (!(strcmp(@$_GET['optin'],1))) {echo "checked=\"checked\"";} ?> name="optin" type="checkbox" id="optin" value="1" />email opt-in only</label>
    	    <input name="groupsetID" type="hidden" id="groupsetID" value="<?php echo htmlentities($_GET['groupsetID']); ?>" />
    	    <input name="csv" type="hidden" id="csv" value="0" />
<button type="submit" name="searchbutton" id="searchbutton" class="btn btn-default btn-secondary" >Search...</button>&nbsp;&nbsp;<img src="/documents/images/document-application--vnd.ms-excel.png" alt="Excel icon" width="16" height="16" style="vertical-align:
middle;" /> <a href="javascript:void(0);" onClick="document.getElementById('csv').value = 1; document.getElementById('form1').submit();">Download all results as spreadsheet</a>
    	    
    	  </fieldset>
      </form>
    	<?php if(isset($limitusers) && mysql_num_rows($limitusers)>0) { // is rows ?>
        <p class="text-muted">Users <?php echo $start+1; ?> to <?php echo $start+$limittotal; ?> of <?php echo $total; ?>:</p>
        <table class="table table-hover"><thead><tr>
        
		  <th>Name</th>
		  <th>Email</th>
		  <th>Opt-in</th>
		  </tr></thead><tbody>
		<?php while($user = mysql_fetch_assoc($limitusers)) {  // count through rows ?>
		
		<tr><td><?php echo $user['firstname']." ".$user['surname']; ?></td><td><?php echo $user['email']; ?></td><td><?php if($user['emailoptin']==1) { ?>&nbsp;<span class="glyphicon glyphicon-ok"></span><?php } ?>&nbsp;</td></tr>
	<?php }  // count through rows ?></tbody>
    </table>
    <?php if($start>0) { ?>
    <a href="<?php echo $url; ?>&start=0&total=<?php echo $total; ?>">First</a>&nbsp;&nbsp;&nbsp;<a href="<?php echo $url; ?>&start=<?php echo $start-$limittotal; ?>&total=<?php echo $total; ?>">Previous</a>&nbsp;&nbsp;&nbsp;
	<?php } ?>
    <?php if($total > $start+$limittotal) { ?>
    <a href="<?php echo $url; ?>&start=<?php echo $start+$limittotal; ?>&total=<?php echo $total; ?>">Next</a>
<?php } ?>
    
	<?php } // // end is rows 
	else { // no rows ?>
    <p>There are no users in this group set.</p>
    <?php } ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisSet);

mysql_free_result($rsUserType);
?>
