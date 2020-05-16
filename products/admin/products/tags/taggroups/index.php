<?php require_once('../../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../../core/includes/adminAccess.inc.php'); ?>
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

if(isset($_GET['delete_taggroupID']) && intval($_GET['delete_taggroupID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM producttaggroup WHERE ID = ".intval($_GET['delete_taggroupID']);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	
	
}

$maxRows_rsTagGroups = 1000;
$pageNum_rsTagGroups = 0;
if (isset($_GET['pageNum_rsTagGroups'])) {
  $pageNum_rsTagGroups = $_GET['pageNum_rsTagGroups'];
}
$startRow_rsTagGroups = $pageNum_rsTagGroups * $maxRows_rsTagGroups;

$varRegionID_rsTagGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsTagGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTagGroups = sprintf("SELECT producttaggroup.ID, producttaggroup.taggroupname, region.title AS sitename, COUNT(producttag.ID) AS num_tags FROM producttaggroup LEFT JOIN region ON (producttaggroup.regionID= region.ID) LEFT JOIN producttag ON (producttag.taggroupID = producttaggroup.ID) WHERE producttaggroup.regionID = %s OR producttaggroup.regionID =0 GROUP BY producttaggroup.ID ORDER BY producttaggroup.ordernum ASC", GetSQLValueString($varRegionID_rsTagGroups, "int"));
$query_limit_rsTagGroups = sprintf("%s LIMIT %d, %d", $query_rsTagGroups, $startRow_rsTagGroups, $maxRows_rsTagGroups);
$rsTagGroups = mysql_query($query_limit_rsTagGroups, $aquiescedb) or die(mysql_error());
$row_rsTagGroups = mysql_fetch_assoc($rsTagGroups);

if (isset($_GET['totalRows_rsTagGroups'])) {
  $totalRows_rsTagGroups = $_GET['totalRows_rsTagGroups'];
} else {
  $all_rsTagGroups = mysql_query($query_rsTagGroups);
  $totalRows_rsTagGroups = mysql_num_rows($all_rsTagGroups);
}
$totalPages_rsTagGroups = ceil($totalRows_rsTagGroups/$maxRows_rsTagGroups)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Tag Groups"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../../css/defaultProducts.css" rel="stylesheet" >
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	<?php if($totalRows_rsTagGroups>0) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=producttaggroup&"+order); 
            } 
        }); 
		<?php } ?>
    }); 
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Manage Tag Groups</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Tags</a></li>
      </ul></div></nav>
<?php if ($totalRows_rsTagGroups == 0) { // Show if recordset empty ?>
        <p>There are no tag groups currently available. Add a tag group when adding a tag.</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsTagGroups > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Tag groups <?php echo ($startRow_rsTagGroups + 1) ?> to <?php echo min($startRow_rsTagGroups + $maxRows_rsTagGroups, $totalRows_rsTagGroups) ?> of <?php echo $totalRows_rsTagGroups ?> <span id="info">Choose a specific section to drag and drop re-order</span></p>
        <table class="table table-hover">
        <thead>
        <tr>
        <th>&nbsp;</th>
         <th>Name</th>
          <th>Tags</th>
           <th>Site</th>
           <th colspan="2">Actions</th>
          
        </tr>
        
        </thead>
        <tbody class="sortable">
          
          <?php do { ?>
             <tr id="listItem_<?php echo $row_rsTagGroups['ID']; ?>" ><td class= "handle" title="Drag and drop order of pages">&nbsp;</td>
             
              <td><?php echo $row_rsTagGroups['taggroupname']; ?></td>
               <td>(<?php echo $row_rsTagGroups['num_tags']; ?>)</td>
                <td><?php echo isset($row_rsTagGroups['sitename']) ? $row_rsTagGroups['sitename'] : "All Sites"; ?></td>
               
               <td><a href="update_tag_group.php?taggroupID=<?php echo $row_rsTagGroups['ID']; ?>" class="link_edit icon_only">Edit</a></td>
              
              <td><?php if($row_rsTagGroups['num_tags']==0) { ?><a href="index.php?delete_taggroupID=<?php echo $row_rsTagGroups['ID']; ?>" class="link_delete">Delete</a><?php } ?></td>
              
              
           </tr>
            <?php } while ($row_rsTagGroups = mysql_fetch_assoc($rsTagGroups)); ?>
       </tbody></table>
        <?php } // Show if recordset not empty ?>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsTagGroups);
?>
