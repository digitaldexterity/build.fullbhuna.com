<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../includes/adminAccess.inc.php'); ?>
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

$maxRows_rsTags = 50;
$pageNum_rsTags = 0;
if (isset($_GET['pageNum_rsTags'])) {
  $pageNum_rsTags = $_GET['pageNum_rsTags'];
}
$startRow_rsTags = $pageNum_rsTags * $maxRows_rsTags;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTags = "SELECT tag.*, COUNT(tagged.ID) AS tagged, taggroup.taggroupname FROM tag LEFT JOIN tagged ON (tag.ID = tagged.tagID) LEFT JOIN taggroup ON (tag.taggroupID = taggroup.ID) GROUP BY tag.ID ORDER BY tag.ordernum, tag.ID";
$query_limit_rsTags = sprintf("%s LIMIT %d, %d", $query_rsTags, $startRow_rsTags, $maxRows_rsTags);
$rsTags = mysql_query($query_limit_rsTags, $aquiescedb) or die(mysql_error());
$row_rsTags = mysql_fetch_assoc($rsTags);

if (isset($_GET['totalRows_rsTags'])) {
  $totalRows_rsTags = $_GET['totalRows_rsTags'];
} else {
  $all_rsTags = mysql_query($query_rsTags);
  $totalRows_rsTags = mysql_num_rows($all_rsTags);
}
$totalPages_rsTags = ceil($totalRows_rsTags/$maxRows_rsTags)-1;

$queryString_rsTags = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsTags") == false && 
        stristr($param, "totalRows_rsTags") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsTags = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsTags = sprintf("&totalRows_rsTags=%d%s", $totalRows_rsTags, $queryString_rsTags);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Tags"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 

        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=tag&"+order); 
            } 
        }); 
		
    }); 
</script>

<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
   
      <div class="page class">
        <h1>Manage Tags</h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li class="nav-item"><a href="add_tag.php" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Tag </a></li>
           <li class="nav-item"><a href="groups/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Manage Tag Groups</a></li>
     </ul></div></nav> <?php if ($totalRows_rsTags > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Tags <?php echo ($startRow_rsTags + 1) ?> to <?php echo min($startRow_rsTags + $maxRows_rsTags, $totalRows_rsTags) ?> of <?php echo $totalRows_rsTags ?>. <span id="info">Drag and drop to re-order.</span></p>
        <table class="table table-hover">
        <thead>
          <tr>
            <th>&nbsp;</th>
           
            <th>Tag</th>
            <th class="taggroup">Group</th>
            <th>Auto</th>
            <th class="region">Site</th>
           
           <th>Count</th>  <th>View</th>
            <th>Edit</th>
          </tr></thead><tbody class="sortable">
          <?php do { ?>
            <tr id="listItem_<?php echo $row_rsTags['ID']; ?>" >
             <td class="handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of tags">&nbsp;</td>
             
              <td><?php echo $row_rsTags['tagname']; ?></td>
              <td class="taggroup"><?php echo $row_rsTags['taggroupname']; ?></td>
              <td class="<?php if($row_rsTags['taggeddefault']==1){ echo "tick1"; } ?>">&nbsp;</td>
              <td class="region"><?php echo $row_rsTags['regionID']; ?></td>
              
            
              <td><a href="tagged.php?tagID=<?php echo $row_rsTags['ID']; ?>"><?php echo $row_rsTags['tagged']; ?></a></td>  <td><a href="tagged.php?tagID=<?php echo $row_rsTags['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i> View</a></td>
               <td><a href="update_tag.php?tagID=<?php echo $row_rsTags['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a></td>
            </tr>
            <?php } while ($row_rsTags = mysql_fetch_assoc($rsTags)); ?></tbody>
        </table>
        <table class="form-table">
          <tr>
            <td><?php if ($pageNum_rsTags > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTags=%d%s", $currentPage, 0, $queryString_rsTags); ?>">First</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsTags > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTags=%d%s", $currentPage, max(0, $pageNum_rsTags - 1), $queryString_rsTags); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_rsTags < $totalPages_rsTags) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTags=%d%s", $currentPage, min($totalPages_rsTags, $pageNum_rsTags + 1), $queryString_rsTags); ?>">Next</a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_rsTags < $totalPages_rsTags) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTags=%d%s", $currentPage, $totalPages_rsTags, $queryString_rsTags); ?>">Last</a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
       
      </div>
      <?php } // Show if recordset not empty ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsTags);
?>
