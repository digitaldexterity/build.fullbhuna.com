<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php

$regionID = isset($regionID) && intval($regionID)>0 ? intval($regionID) : 1;
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

$currentPage = $_SERVER["PHP_SELF"];

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
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".intval($regionID);
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);



if(isset($_GET['deleteformID'])) {
	$update = "UPDATE form SET statusID = 0 WHERE ID = ".intval($_GET['deleteformID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

if(isset($_GET['duplicateformID'])) {
	$newformID = duplicateMySQLRecord ("form", $_GET['duplicateformID']);
	if($newformID>0) {
		$select = "SELECT ID FROM formfield WHERE formID = ".intval($_GET['duplicateformID']);
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$fields = mysql_query($select, $aquiescedb) or die(mysql_error());
		while($field = mysql_fetch_assoc($fields)) {
			$newformfieldID = duplicateMySQLRecord ("formfield", $field['ID']);
			if($newformfieldID>0) {
				$update = "UPDATE formfield SET formID = ".$newformID.", createdbyID = ".$row_rsLoggedIn['ID'].", createddatetime = '".date('Y-m-d H:i:s')."'  WHERE ID = ".$newformfieldID;
				mysql_query($update, $aquiescedb) or die(mysql_error());			
				$select = "SELECT ID FROM formfieldchoice  WHERE formfieldID = ".$field['ID'];
				$choices = mysql_query($select, $aquiescedb) or die(mysql_error());
				while($choice = mysql_fetch_assoc($choices)) {
					$newchoiceID = duplicateMySQLRecord ("formfieldchoice", $choice['ID']);
					if($newchoiceID>0) {
						$update = "UPDATE formfieldchoice SET formfieldID = ".$newformfieldID.", createdbyID = ".$row_rsLoggedIn['ID'].", createddatetime = '".date('Y-m-d H:i:s')."' WHERE ID = ".$newchoiceID;
						mysql_query($update, $aquiescedb) or die(mysql_error());
					}
				}
			}
		}
		header("location: update_form.php?formID=".$newformID); exit;
	} // is new form
}



$maxRows_rsForms = 50;
$pageNum_rsForms = 0;
if (isset($_GET['pageNum_rsForms'])) {
  $pageNum_rsForms = $_GET['pageNum_rsForms'];
}
$startRow_rsForms = $pageNum_rsForms * $maxRows_rsForms;

$varRegionID_rsForms = "1";
if (isset($regionID)) {
  $varRegionID_rsForms = $regionID;
}
$varShowInactive_rsForms = "0";
if (isset($_GET['showinactive'])) {
  $varShowInactive_rsForms = $_GET['showinactive'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForms = sprintf("SELECT `form`.*, COUNT(formresponse.ID) AS numresponses FROM `form` LEFT JOIN formresponse ON (formresponse.formID = `form`.ID) WHERE regionID = %s AND (%s = 1 OR `form`.statusID = 1) GROUP BY `form`.ID", GetSQLValueString($varRegionID_rsForms, "int"),GetSQLValueString($varShowInactive_rsForms, "int"));
$query_limit_rsForms = sprintf("%s LIMIT %d, %d", $query_rsForms, $startRow_rsForms, $maxRows_rsForms);
$rsForms = mysql_query($query_limit_rsForms, $aquiescedb) or die(mysql_error());
$row_rsForms = mysql_fetch_assoc($rsForms);

if (isset($_GET['totalRows_rsForms'])) {
  $totalRows_rsForms = $_GET['totalRows_rsForms'];
} else {
  $all_rsForms = mysql_query($query_rsForms);
  $totalRows_rsForms = mysql_num_rows($all_rsForms);
}
$totalPages_rsForms = ceil($totalRows_rsForms/$maxRows_rsForms)-1;

$queryString_rsForms = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsForms") == false && 
        stristr($param, "totalRows_rsForms") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsForms = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsForms = sprintf("&totalRows_rsForms=%d%s", $totalRows_rsForms, $queryString_rsForms);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Forms"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --> <div class="page class">
      <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-th-list"></i> Manage Forms</h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="update_form.php" ><i class="glyphicon glyphicon-plus-sign"></i> Create Form</a></li>
        </ul></div></nav>
      

 <form method="get">
 <p><?php if ($totalRows_rsForms > 0) { // Show if recordset not empty ?>Forms <?php echo ($startRow_rsForms + 1) ?> to <?php echo min($startRow_rsForms + $maxRows_rsForms, $totalRows_rsForms) ?> of <?php echo $totalRows_rsForms ?><?php } else { ?>No forms to show<?php } ?> <label><input name="showinactive" value="1" type="checkbox"  <?php echo isset($_GET['showinactive']) ? "checked" : ""; ?> onClick="this.form.submit()"> Show inactive forms</label></p></form><?php if ($totalRows_rsForms > 0) { // Show if recordset not empty ?>
          <table class="table table-hover">  <thead>          <tr>
            <th>&nbsp;</th>
              <th>Form</th>
              
              <th>Recipient</th>
              <th>Add-in</th>
              <th>Responses</th>
              
              <th>Actions</th>
              </tr></thead><tbody>
            <?php do { ?>
              <tr>
               <td class="status<?php echo $row_rsForms['statusID']; ?>">&nbsp;</td>
                <td><?php echo $row_rsForms['formname']; ?></td>
                
                <td><?php echo isset($row_rsForms['email']) ? $row_rsForms['email'] : ""; ?></td>
                <td>{formbuilder<?php echo $row_rsForms['ID']; ?>}</td>
                <td nowrap><a href="responses.php?formID=<?php echo $row_rsForms['ID']; ?>" class="btn btn-sm btn-default btn-secondary"  data-toggle="tooltip" title="View form responses"><i class="glyphicon glyphicon-search"></i> View <?php echo $row_rsForms['numresponses']; ?></a></td>
                
                
                <td nowrap="nowrap"><div class="btn-group"><a href="update_form.php?formID=<?php echo $row_rsForms['ID']; ?>" class="btn btn-sm btn-default btn-secondary"  data-toggle="tooltip" title="Edit this form"><i class="glyphicon glyphicon-pencil"></i> Edit</a>  <a href="index.php?duplicateformID=<?php echo $row_rsForms['ID']; ?>"  onClick="return confirm('Are you sure you want to duplicate this form?')" title="Create a new form identical to this form which can then be edited" data-toggle="tooltip" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-plus-sign"></i> Duplicate</a> <a href="/forms/form.php?formID=<?php echo $row_rsForms['ID']; ?>" title="Open form in new window" target="_blank" class="btn btn-sm btn-default btn-secondary" rel="noopener"  data-toggle="tooltip" ><i class="glyphicon glyphicon-new-window"></i> Open</a> <a href="javascript:void(0);" onClick="prompt('You can copy the URL for this page to your clipboard below:','<?php 
	$url = getProtocol()."://".$_SERVER['HTTP_HOST']; 
	
	 $url .= "/forms/form.php?formID=".$row_rsForms['ID'];  echo $url; ?>')" class="btn btn-sm btn-default btn-secondary" title="Link to this page" ><i class="glyphicon glyphicon-link"></i> Link</a> <a href="index.php?deleteformID=<?php echo $row_rsForms['ID']; ?>" class="btn btn-sm btn-default btn-secondary"  data-toggle="tooltip" title="Delete this form" onClick="return confirm('Are you sure you want to delete this form?');"><i class="glyphicon glyphicon-trash"></i> Delete</a> </div></td>
    
              </tr>
              <?php } while ($row_rsForms = mysql_fetch_assoc($rsForms)); ?></tbody>
          </table>
          <?php } // Show if recordset not empty ?>
         <?php echo createPagination($pageNum_rsForms,$totalPages_rsForms,"rsForms");?>  
    </div>
        <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsForms);
?>
