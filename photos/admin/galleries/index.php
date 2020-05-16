<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$maxRows_rsGalleries = 100;
$pageNum_rsGalleries = 0;
if (isset($_GET['pageNum_rsGalleries'])) {
  $pageNum_rsGalleries = $_GET['pageNum_rsGalleries'];
}
$startRow_rsGalleries = $pageNum_rsGalleries * $maxRows_rsGalleries;

$varRegionID_rsGalleries = "1";
if (isset($regionID)) {
  $varRegionID_rsGalleries = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT photocategories.*, parentcategory.categoryname AS parentname, usertype.name AS rank, usergroup.groupname, (SELECT COUNT(ID) FROM photos WHERE photos.categoryID = photocategories.ID GROUP BY  photos.categoryID) AS numphotos, (SELECT photos.imageURL FROM photos WHERE photos.categoryID = photocategories.ID ORDER BY photocategories.coverphotoID = photos.ID DESC LIMIT 1) AS imageURL  FROM photocategories  LEFT JOIN photocategories AS parentcategory ON (photocategories.categoryofID = parentcategory.ID) LEFT JOIN usertype ON photocategories.accesslevel = usertype.ID LEFT JOIN usergroup ON (photocategories.groupID = usergroup.ID) WHERE photocategories .regionID = %s ORDER BY ordernum ASC", GetSQLValueString($varRegionID_rsGalleries, "int"));
$query_limit_rsGalleries = sprintf("%s LIMIT %d, %d", $query_rsGalleries, $startRow_rsGalleries, $maxRows_rsGalleries);
$rsGalleries = mysql_query($query_limit_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);

if (isset($_GET['totalRows_rsGalleries'])) {
  $totalRows_rsGalleries = $_GET['totalRows_rsGalleries'];
} else {
  $all_rsGalleries = mysql_query($query_rsGalleries);
  $totalRows_rsGalleries = mysql_num_rows($all_rsGalleries);
}
$totalPages_rsGalleries = ceil($totalRows_rsGalleries/$maxRows_rsGalleries)-1;

$queryString_rsGalleries = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsGalleries") == false && 
        stristr($param, "totalRows_rsGalleries") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsGalleries = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsGalleries = sprintf("&totalRows_rsGalleries=%d%s", $totalRows_rsGalleries, $queryString_rsGalleries);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Galleries"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
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
                $("#info").load("/core/ajax/sort.ajax.php?table=photocategories&"+order); 
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
    <!-- InstanceBeginEditable name="Body" --><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <div class="page photos">
      <h1><i class="glyphicon glyphicon-picture"></i> Manage Galleries</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     
        <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Photos</a></li>   <li><a href="add_gallery.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Gallery</a></li>
      </ul></div></nav>
<?php if ($totalRows_rsGalleries == 0) { // Show if recordset empty ?>
        <p>There are currently no galleries</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsGalleries > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Galleries <?php echo ($startRow_rsGalleries + 1) ?> to <?php echo min($startRow_rsGalleries + $maxRows_rsGalleries, $totalRows_rsGalleries) ?> of <?php echo $totalRows_rsGalleries ?>. <span id="info">Drag and drop to re-order.</span></p>
        <table class="table table-hover">
          <thead>
          <tr>
            <th>&nbsp;</th><th>&nbsp;</th>  <th>ID</th><th>Cover</th>
            <th>Gallery name</th>
            
            
            
            <th>Parent</th><th>Photos</th> <th>Status</th>
            <th>Access</th>
            <th>Actions</th>
          </tr></thead><tbody class="sortable">
          <?php do { ?>
            <tr id="listItem_<?php echo $row_rsGalleries['ID']; ?>"><td class="handle">&nbsp;</td>
              <td class="status<?php echo $row_rsGalleries['active']; ?>">&nbsp;</td>  <td><?php echo $row_rsGalleries['ID']; ?></td><td><a class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsGalleries['imageURL'],"thumb"); ?>)"></a></td>
              <td><?php echo $row_rsGalleries['categoryname']; ?></td>
              
              
              
              <td><?php echo isset($row_rsGalleries['parentname']) ? $row_rsGalleries['parentname'] : "&nbsp;"; ?></td>
               <td><?php echo intval($row_rsGalleries['numphotos']); ?></td>
              <td><em><?php switch($row_rsGalleries['active']) {
				  case 0 : echo "Unlisted"; break;
				  case 2 : echo "Off";break;
				  default : echo "Listed";
			  }
			  ?></em></td>
              <td><em><?php echo ($row_rsGalleries['accesslevel']==0) ?   "Everyone" : $row_rsGalleries['rank'] ; ?> <?php echo $row_rsGalleries['groupname']; ?></em></td>
              <td><a href="update_gallery.php?galleryID=<?php echo $row_rsGalleries['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i> Edit</a></td>
          </tr>
            <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?></tbody></table>
        
        <?php } // Show if recordset not empty ?>
      
<table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsGalleries > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, 0, $queryString_rsGalleries); ?>">First</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsGalleries > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, max(0, $pageNum_rsGalleries - 1), $queryString_rsGalleries); ?>">Previous</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsGalleries < $totalPages_rsGalleries) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, min($totalPages_rsGalleries, $pageNum_rsGalleries + 1), $queryString_rsGalleries); ?>">Next</a>
              <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsGalleries < $totalPages_rsGalleries) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsGalleries=%d%s", $currentPage, $totalPages_rsGalleries, $queryString_rsGalleries); ?>">Last</a>
              <?php } // Show if not last page ?></td>
        </tr>
      </table>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGalleries);
?>
