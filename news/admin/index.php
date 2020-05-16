<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../includes/newsfunctions.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "8,9,10";
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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


if(isset($_GET['duplicatenewsID'])) {
	$newID = duplicateMySQLRecord ("news", $_GET['duplicatenewsID']) ;
	$update = "UPDATE news  SET status = 0, modifiedbyID = ".intval($adminUser['ID']).", modifieddatetime=NOW() WHERE ID = ".$newID;
 	mysql_query($update, $aquiescedb) or die(mysql_error());
	header("location: update_news.php?newsID=".$newID); exit;
}

if(isset($_GET['deletenewsID'])) {
	
	$update = "UPDATE news  SET status = 3, modifiedbyID = ".intval($adminUser['ID']).", modifieddatetime=NOW() WHERE ID = ".intval($_GET['deletenewsID']);
 	mysql_query($update, $aquiescedb) or die(mysql_error());
}




$currentPage = $_SERVER["PHP_SELF"];


$varRegionID_rsSections = "1";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT ID, sectioname FROM newssection WHERE statusID = 1 AND (newssection.regionID = 0 OR newssection.regionID = %s) ORDER BY newssection.ordernum, newssection.ID", GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);


$defaultsection = isset($row_rsSections['ID']) ? $row_rsSections['ID'] : "-1";

$varThisSection_rsThisSection = $defaultsection;
if (isset($_GET['sectionID'])) {
  $varThisSection_rsThisSection = $_GET['sectionID'];
}
$varRegionID_rsThisSection = "1";
if (isset($regionID)) {
  $varRegionID_rsThisSection = $regionID;
}
$varThisSection_rsThisSection = "-1";
if (isset($_GET['sectionID'])) {
  $varThisSection_rsThisSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT sectioname, orderby, newssection.ID, newssection.longID FROM newssection WHERE ID = %s OR (%s <1 AND (newssection.regionID = 0 OR newssection.regionID = %s)) ORDER BY ID LIMIT 1", GetSQLValueString($varThisSection_rsThisSection, "int"),GetSQLValueString($varThisSection_rsThisSection, "int"),GetSQLValueString($varRegionID_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);

switch($row_rsThisSection['orderby']) {
	case 1: $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // most recent
	case 2: $orderby = "ORDER BY headline DESC,  displayfrom ASC"; break; // date posted
	case 3: $orderby = "ORDER BY headline DESC, news.ordernum ASC, displayfrom DESC"; break; // draggable 
	default : $orderby = "ORDER BY headline DESC,  displayfrom DESC"; break; // default most recent
}


$maxRows_rsNews = 100;
$pageNum_rsNews = 0;
if (isset($_GET['pageNum_rsNews'])) {
  $pageNum_rsNews = $_GET['pageNum_rsNews'];
}
$startRow_rsNews = $pageNum_rsNews * $maxRows_rsNews;

$varSectionID_rsNews = $defaultsection;
if (isset($_GET['sectionID'])) {
  $varSectionID_rsNews = $_GET['sectionID'];
}
$varShowAll_rsNews = "0";
if (isset($_GET['showall'])) {
  $varShowAll_rsNews = $_GET['showall'];
}
$varRegionID_rsNews = "1";
if (isset($regionID)) {
  $varRegionID_rsNews = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNews = sprintf("SELECT news.ID,news.longID, news.title, news.imageURL, displayfrom, displayto, newssection.sectioname, news.sectionID, news.status, news.rss, news.groupemailID, news.alert, region.title AS region, news.headline FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) LEFT JOIN region ON (news.regionID = region.ID) WHERE (%s=1 OR (news.status < 2 AND (displayto >=CURDATE() OR displayto IS NULL OR displayto =''))) AND news.sectionID = %s AND (news.regionID = 0 OR news.regionID  IS NULL OR news.regionID = %s OR %s = 0 OR newssection.regionID = 0) ".$orderby."", GetSQLValueString($varShowAll_rsNews, "int"),GetSQLValueString($varSectionID_rsNews, "int"),GetSQLValueString($varRegionID_rsNews, "int"),GetSQLValueString($varRegionID_rsNews, "int"));
$query_limit_rsNews = sprintf("%s LIMIT %d, %d", $query_rsNews, $startRow_rsNews, $maxRows_rsNews);
$rsNews = mysql_query($query_limit_rsNews, $aquiescedb) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);

if (isset($_GET['totalRows_rsNews'])) {
  $totalRows_rsNews = $_GET['totalRows_rsNews'];
} else {
  $all_rsNews = mysql_query($query_rsNews);
  $totalRows_rsNews = mysql_num_rows($all_rsNews);
}
$totalPages_rsNews = ceil($totalRows_rsNews/$maxRows_rsNews)-1;



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$queryString_rsNews = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsNews") == false && 
        stristr($param, "totalRows_rsNews") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsNews = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsNews = sprintf("&totalRows_rsNews=%d%s", $totalRows_rsNews, $queryString_rsNews);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsPrefs = "SELECT * FROM newsprefs";
$rsNewsPrefs = mysql_query($query_rsNewsPrefs, $aquiescedb) or die(mysql_error());
$row_rsNewsPrefs = mysql_fetch_assoc($rsNewsPrefs);
$totalRows_rsNewsPrefs = mysql_num_rows($rsNewsPrefs);

$varNewsID_rsAllTags = "-1";
if (isset($_GET['newsID'])) {
  $varNewsID_rsAllTags = $_GET['newsID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAllTags = sprintf("SELECT tag.ID, tagname, taggroup.taggroupname, tagged.tagID AS tagged FROM tag LEFT JOIN taggroup ON (tag. taggroupID = taggroup.ID) LEFT JOIN tagged ON (tagged.tagID =tag.ID AND newsID = %s) ORDER BY taggroupID, tagname ASC", GetSQLValueString($varNewsID_rsAllTags, "int"));
$rsAllTags = mysql_query($query_rsAllTags, $aquiescedb) or die(mysql_error());
$row_rsAllTags = mysql_fetch_assoc($rsAllTags);
$totalRows_rsAllTags = mysql_num_rows($rsAllTags);
?>
<?php 

if($totalRows_rsNewsPrefs==0) {
	duplicateMySQLRecord ("newsprefs", 1, "ID", $regionID) ;
	header("location: index.php"); exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
    <title>
    <?php $pageTitle = "Manage Posts"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
    </title>
    <!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
    <script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=news&"+order); 
            } 
        }); 
		
    }); 
	
	
</script>
<style><!--
<?php if($row_rsThisSection['orderby']!=3) {
	echo ".draganddrop { display:none !important; } ";
} 
 if($totalRows_rsAllTags==0) {
	 echo ".tags { display:none !important; } ";
 }
 ?>
--></style>
    <!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page news"><?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-bullhorn"></i> Posts Manager</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li class="nav-item"><a href="add_news.php?sectionID=<?php echo isset($row_rsThisSection['ID']) ? $row_rsThisSection['ID'] : 1; ?>" class="nav-link" ><i class="glyphicon glyphicon-plus-sign"></i> Add New Post</a></li>
            <li class="nav-item"><a href="sections/index.php" class="nav-link" ><i class="glyphicon glyphicon-list"></i> Manage Sections</a></li>
            <li class="nav-item"><a href="/core/seo/admin/autolinks/index.php" class="nav-link" ><i class="glyphicon glyphicon-link"></i> Manage Autolinks</a></li>  <li><a href="/core/tags/admin/index.php" class="nav-link" ><i class="glyphicon glyphicon-tags"></i> Manage Tags</a></li>
            <li class="nav-item"><a href="options/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Post Options</a></li>
          </ul></div></nav><?php require_once('../../core/includes/alert.inc.php'); ?>
     
      <form action="index.php" method="get" name="form1" id="form1" class="form-inline">
            
                        <?php if ($totalRows_rsSections > 1) { // Show if recordset not empty ?><label class="section-filter">Filter by:
            <select name="sectionID"  id="sectionID" onChange="this.form.submit();" class="form-control">
          <?php
do {  
?>
          <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['sectioname']?></option>
          <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
        </select></label>
            <?php } // Show if recordset not empty  } ?>
          <label>
              <input type="checkbox" name="showall" id="showall" value="1" <?php if(isset($_GET['showall'])) echo "checked"; ?> onClick="this.form.submit();">
              Show inactive Posts</label> &nbsp;&nbsp;&nbsp;
              
               
      </form>
   
      <?php if ($totalRows_rsNews == 0) { // Show if recordset empty ?>
        <p>There are no posts to show. </p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsNews > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Posts <?php echo ($startRow_rsNews + 1) ?> to <?php echo min($startRow_rsNews + $maxRows_rsNews, $totalRows_rsNews) ?> of <?php echo $totalRows_rsNews ?> <span id="info" class="draganddrop">Drag and drop handles to re-order</span></p>
       <table class="table table-hover"><thead>          <tr> <th class= "draganddrop" >&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th class="images">&nbsp;</th> <th>Title</th> <th class="text-nowrap">Display from</th> <th class="text-nowrap">Display until</th> <th>Section</th>     <th data-toggle="tooltip" title="Shows number of recent page hits. Click on number for more detailed stats">Visits</th>  <th class="tags">Tags</th> <th class="region">Site</th> <th>Actions</th> </tr></thead><tbody class="sortable">
            <?php do { ?>
            
          <tr  id="listItem_<?php echo $row_rsNews['ID']; ?>" ><td class= "handle draganddrop" data-toggle="tooltip" data-placement="right" title="Drag and drop order of pages">&nbsp;</td><td class="status<?php if ((!isset($row_rsNews['displayfrom']) || $row_rsNews['displayfrom'] <= date('Y-m-d H:i:s')) && (!isset($row_rsNews['displayto']) || $row_rsNews['displayto'] >= date('Y-m-d H:i:s')) && $row_rsNews['status'] ==1)  { echo "1"; } else if ($row_rsNews['status'] ==0) { echo "0"; } else { echo "2"; } ?>">
            
            </td> <td>
             <?php if($row_rsNews['alert']==1) { ?>
            <i class="glyphicon glyphicon-warning-sign"></i>
            <?php } ?>
            <?php if($row_rsNews['headline']==1) { ?>
           <i class="glyphicon glyphicon-star"></i>
            <?php } ?>
            <?php if($row_rsNews['rss']==1) { ?>
            <img src="/core/images/icons/feed-icon-16x16.png" alt="Included in RSS feed" width="16" height="16" style="vertical-align:
middle;" />
            <?php } ?>
            <?php if($row_rsNews['groupemailID']>0) { ?>
             <i class="glyphicon glyphicon-envelope"></i>
            <?php } ?>
            
            
           
           &nbsp;</td>
            
            <td class="images" ><span class="fb_avatar" style="background-image:url(<?php echo getImageURL($row_rsNews['imageURL'], "thumb"); ?>); width:32px; height:32px; vertical-align:
middle;" ></span></td>
            
            
            <td>
            <a href="update_news.php?newsID=<?php echo $row_rsNews['ID']; ?>&amp;sectionID=<?php echo isset($row_rsThisSection['ID']) ? $row_rsThisSection['ID'] : 1; ?>"><?php echo $row_rsNews['title']; ?></a></td> <td class="text-nowrap" ><?php echo isset($row_rsNews['displayfrom']) ? date('d M Y',strtotime($row_rsNews['displayfrom'])) : '-'; ?></td> <td class="text-nowrap" ><?php echo isset($row_rsNews['displayto']) ? date('d M Y',strtotime($row_rsNews['displayto'])) : 'Forever'; ?></td> <td><em><?php echo $row_rsNews['sectioname']; ?></em></td>
       
    
        <?php $newsLink=newsLink($row_rsNews['ID'], $row_rsNews['longID'], $row_rsThisSection['ID'], $row_rsThisSection['longID']) ; ?>
        
        
        <td><a href="/core/seo/admin/visitors/page.php?page=<?php echo urlencode($newsLink); ?>" ><span id="visitor_count_<?php echo $row_rsNews['ID']; ?>"><script>
              ajaxManager.addReq({
           type: 'GET',
           url: '/core/seo/admin/visitors/ajax/visitors.ajax.php?page=<?php echo urlencode($newsLink); ?>',
          
           success: function(data){
              $("#visitor_count_<?php echo $row_rsNews['ID']; ?>").html(data);
		   }
       });
       </script></span></a></td>    
            <td class="tags"><?php if($totalRows_rsAllTags>0) {
				$select= "SELECT tag.tagname FROM tag LEFT JOIN tagged ON (tag.ID = tagged.tagID)  WHERE newsID = ".$row_rsNews['ID']; 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			while($row = mysql_fetch_assoc($result)) {
				echo $row['tagname']."; ";
			}
		}
		
				
				} ?></td>
            
             <td class="region"><?php echo isset($row_rsNews['region']) ? $row_rsNews['region'] : "All"; ?></td> <td><div class="btn-group" role="group" ><a href="update_news.php?newsID=<?php echo $row_rsNews['ID']; ?>&amp;sectionID=<?php echo isset($row_rsThisSection['ID']) ? $row_rsThisSection['ID'] : 1; ?>" class="btn btn-sm btn-default btn-secondary" data-toggle="tooltip" title="Edit this post"><i class="glyphicon glyphicon-pencil"></i> Edit</a> 
            
            
            
<div class="btn-group">
  <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="glyphicon glyphicon-cog"></i> Actions <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
  <li><a href="/news/story.php?newsID=<?php echo $row_rsNews['ID']+100; ?>&newssectionID=<?php echo isset($row_rsThisSection['ID']) ? $row_rsThisSection['ID'] : 1; ?>&preview=true" target="_blank"  rel="noopener"  title="View this post" data-toggle="tooltip"><i class="glyphicon glyphicon-search"></i> Preview</a></li>
    <li><a href="index.php?sectionID=<?php echo $row_rsNews['sectionID']; ?>&duplicatenewsID=<?php echo $row_rsNews['ID']; ?>" onClick="return confirm('Duplicate Post\n\nAre you sure you want to create a new post based on this post?');"><i class="glyphicon glyphicon-duplicate"></i> Duplicate</a></li>
     <li><a href="index.php?sectionID=<?php echo $row_rsNews['sectionID']; ?>&deletenewsID=<?php echo $row_rsNews['ID']; ?>" onClick="return confirm('Are you sure you want to remove this post?');"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
    

    
  </ul>
</div><!-- end button group-->
</div><!-- end button group-->
            
            
            
            
            </td> </tr>
          <?php } while ($row_rsNews = mysql_fetch_assoc($rsNews)); ?>
       </tbody></table>
        <?php } // Show if recordset not empty ?>
      
    <?php echo createPagination($pageNum_rsNews,$totalPages_rsNews,"rsNews");?>  
      </div>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsNews);

mysql_free_result($rsThisSection);

mysql_free_result($rsSections);

mysql_free_result($rsRegions);

mysql_free_result($rsAllTags);
?>
