<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/functions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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




if(isset($_GET['deletesectionID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM articlesection WHERE ID = ".intval($_GET['deletesectionID']);
	mysql_query($delete, $aquiescedb);
	
}

$varRegionID_rsSections = "1";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT articlesection.*, usertype.name AS readrankname, region.title AS regionname, COUNT(article.ID) AS numarticles, parent.description AS parentname, readgroup.groupname AS readgroupname, writegroup.groupname AS writegroupname, writerank.name AS writerankname, approverank.name AS approverankname FROM articlesection LEFT JOIN usertype ON (articlesection.accesslevel = usertype.ID) LEFT JOIN region ON (articlesection.regionID = region.ID) LEFT JOIN article ON (article.sectionID = articlesection.ID AND article.versionofID IS NULL) LEFT JOIN articlesection AS parent ON (articlesection.subsectionofID = parent.ID) LEFT JOIN usergroup AS readgroup ON (articlesection.groupreadID = readgroup.ID) LEFT JOIN usergroup AS writegroup ON (articlesection.groupwriteID = writegroup.ID) LEFT JOIN usertype AS writerank ON (articlesection.writerankID= writerank.ID) LEFT JOIN usertype AS approverank ON (articlesection.approverankID= approverank.ID) WHERE articlesection.regionID= 0 OR articlesection.regionID = %s GROUP BY articlesection.ID ORDER BY articlesection.ordernum, articlesection.ID ", GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT ID, useregions, usesections FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if(defined("SAVE_MENU") && $regionID ==1) { // save menu as include rather than querying database
		$html = buildMenu(0,4);
		saveFile(UPLOAD_ROOT."menu/fullMenu.inc.php",$html);
	}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Sections"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php 
   if (strcmp($row_rsPreferences['useregions'],1)) { ?>
<style>
.region {
	display: none;
}
</style>
<?php }?>
<link href="../../css/defaultArticles.css" rel="stylesheet"  />
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
<script>!(jQuery.ui) && document.write('<script src="/3rdparty/jquery/jquery-ui-1.10.1.custom.min.js"><\/script>')</script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            	var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=articlesection&"+order); 
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
        <div class="page articles">
        <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
        <h1><i class="glyphicon glyphicon-file"></i> Manage  Sections</h1>
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="add_section.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Section</a></li>
          <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Pages</a></li>
        </ul></div></nav>
        <?php if ($totalRows_rsSections == 0) { // Show if recordset empty ?>
          <p>No sections created so far.</p>
          <?php } // Show if recordset empty ?>
        <?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
          <p class="text-muted" id="info">Drag and drop to re-order</p><div class="table-responsive">
          <table class="table table-hover">
          <thead>
            <tr><th colspan="3" class="text-nowrap">Parent &gt; Section Name (pages)</th> 
            <th class="text-nowrap">Can View</th> <th class="text-nowrap">Can Edit</th> <th  data-toggle="tooltip" title="By default pages can be posted live by anyone who can create and edit them. If you choose an approval rank, the page can only be saved as draft by anyone below that rank."  class="text-nowrap">Approval by</th> <th class="region">Site</th>   <th>&nbsp;</th>   <th>&nbsp;</th>   <th>&nbsp;</th>   <th>&nbsp;</th>    <th  class="rank10">Admin ID</th><th  class="rank10">Admin Ord</th></tr></thead>
             <tbody class="sortable">
            <?php do { ?>
              <tr  id="listItem_<?php echo $row_rsSections['ID']; ?>"> <td class="handle">&nbsp;</td> <td>
                <?php if ($row_rsSections['showlink'] == 1) { ?>
               
                 <span class="text-success" title="Shows in all menus" data-toggle="tooltip">&#9679;</span>
				 <?php } else if ($row_rsSections['showlink'] == 0) { ?>
 <span class="text-warning" title="Shows in site map only" data-toggle="tooltip">&#9679;</span>
				 
				 <?php } else { ?>
           
<span class="text-warning" title="Does not show in navigation or site map" data-toggle="tooltip">&#9679;</span>
                <?php } ?>
                </td> 
                
                
                 
                 
                 
                 <td><?php echo isset($row_rsSections['parentname']) ? $row_rsSections['parentname']." > " : ""; ?><a href="update_section.php?sectionID=<?php echo $row_rsSections['ID']; ?>"><?php echo $row_rsSections['description']; ?></a> (<?php echo $row_rsSections['numarticles']; ?>)</td> 
                
                
                <td><em><?php echo ($row_rsSections['accesslevel']>0) ? $row_rsSections['readrankname'] : "All ranks"; ?><?php echo (isset($row_rsSections['readgroupname'])) ? "/".$row_rsSections['readgroupname'] : ""; ?></em></td>
                
                
                <td><em><?php echo ($row_rsSections['writerankID']>0) ? $row_rsSections['writerankname'] : "All ranks"; ?><?php echo (isset($row_rsSections['writegroupname'])) ? "/".$row_rsSections['writegroupname'] : ""; ?></em></td>
                
                
                <td><em><?php echo ($row_rsSections['approverankID']>0) ? $row_rsSections['approverankname'] : "No one"; ?></em></td>
                
                
                
                <td class="region">
                <?php  echo ($row_rsSections['regionID']==0) ? "ALL" : $row_rsSections['regionname']; ?>
                </td> <td><a href="update_section.php?sectionID=<?php echo $row_rsSections['ID']; ?>" class="link_edit icon_only">Edit</a></td> <td><a href="../index.php?sectionID=<?php echo $row_rsSections['ID']; ?>" class="link_view">View</a></td> <td><a href="../add_article.php?sectionID=<?php echo $row_rsSections['ID']; ?>" data-toggle="tooltip" title="Add an article to this section" class="link_add icon_only">Add</a></td> <td><a href="<?php echo ($row_rsSections['numarticles']==0) ? "index.php?deletesectionID=".$row_rsSections['ID'] : "javascript:void(0);"; ?>" class="link_delete" onclick="<?php echo ($row_rsSections['numarticles']==0) ? "return confirm('Are you sure you want to delete this section?')" : "alert('You can only delete a section that does not contain articles. Please remove any articles from this section first.'); return false;"; ?>"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
                
              <td  class="rank10"><?php echo $row_rsSections['ID']; ?></td><td  class="rank10"><?php echo $row_rsSections['ordernum']; ?></td>
                 
                
                 </tr>
              <?php } while ($row_rsSections = mysql_fetch_assoc($rsSections)); ?></tbody>
          </table></div>
          <?php } // Show if recordset not empty ?>
        </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSections);

mysql_free_result($rsPreferences);
?>
