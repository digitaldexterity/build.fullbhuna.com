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

$maxRows_rsResources = 50;
$pageNum_rsResources = 0;
if (isset($_GET['pageNum_rsResources'])) {
  $pageNum_rsResources = $_GET['pageNum_rsResources'];
}
$startRow_rsResources = $pageNum_rsResources * $maxRows_rsResources;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResources = "SELECT eventresource.*, eventcategory.title FROM eventresource LEFT JOIN eventcategory ON (eventresource.categoryID = eventcategory.ID) ORDER BY ordernum ASC, resourcename ASC";
$query_limit_rsResources = sprintf("%s LIMIT %d, %d", $query_rsResources, $startRow_rsResources, $maxRows_rsResources);
$rsResources = mysql_query($query_limit_rsResources, $aquiescedb) or die(mysql_error());
$row_rsResources = mysql_fetch_assoc($rsResources);

if (isset($_GET['totalRows_rsResources'])) {
  $totalRows_rsResources = $_GET['totalRows_rsResources'];
} else {
  $all_rsResources = mysql_query($query_rsResources);
  $totalRows_rsResources = mysql_num_rows($all_rsResources);
}
$totalPages_rsResources = ceil($totalRows_rsResources/$maxRows_rsResources)-1;

$queryString_rsResources = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsResources") == false && 
        stristr($param, "totalRows_rsResources") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsResources = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsResources = sprintf("&totalRows_rsResources=%d%s", $totalRows_rsResources, $queryString_rsResources);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Resources"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=eventresource&"+order); 
            } 
        }); 
		
    }); 
</script>
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
    <!-- InstanceBeginEditable name="Body" --><div class="page calendar"><h1><i class="glyphicon glyphicon-calendar"></i> Manage Resources</h1>
          <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li><a href="add_resource.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Resource</a></li>
            <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Calendar</a></li>
          </ul></div></nav>
          <?php if ($totalRows_rsResources == 0) { // Show if recordset empty ?>
  <p>Sorry, there are no resources in teh syste, at present.</p>
  <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsResources > 0) { // Show if recordset not empty ?>
            <p>Resources <?php echo ($startRow_rsResources + 1) ?> to <?php echo min($startRow_rsResources + $maxRows_rsResources, $totalRows_rsResources) ?> of <?php echo $totalRows_rsResources ?> <span id="info">Drag and drop to re-order</span></p>
           <ul class="listTable sortable">
  <li class="header"> <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>Resource</span>
                <span>Category</span>
              
                <span>Edit</span>
             </li>
              <?php do { ?>
               <li  id="listItem_<?php echo $row_rsResources['ID']; ?>" ><span class= "handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of resources">&nbsp;</span>
                  <span class="status<?php echo $row_rsResources['statusID']; ?>">&nbsp;</span>
                  <span><?php echo $row_rsResources['resourcename']; ?></span>
                  <span><em><?php echo isset($row_rsResources['title']) ? $row_rsResources['title'] : "All"; ?></em></span>
                
                  <span><a href="update_resource.php?resourceID=<?php echo $row_rsResources['ID']; ?>" class="link_edit icon_only">Edit</a></span>
               </li>
                <?php } while ($row_rsResources = mysql_fetch_assoc($rsResources)); ?>
            </ul><table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsResources > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, 0, $queryString_rsResources); ?>">First</a>
                  <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsResources > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, max(0, $pageNum_rsResources - 1), $queryString_rsResources); ?>">Previous</a>
                  <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsResources < $totalPages_rsResources) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, min($totalPages_rsResources, $pageNum_rsResources + 1), $queryString_rsResources); ?>">Next</a>
                  <?php } // Show if not last page ?></td>
              <td><?php if ($pageNum_rsResources < $totalPages_rsResources) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsResources=%d%s", $currentPage, $totalPages_rsResources, $queryString_rsResources); ?>">Last</a>
                  <?php } // Show if not last page ?></td>
            </tr>
          </table>
            <?php } // Show if recordset not empty ?>
         
        </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsResources);
?>
