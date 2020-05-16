<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../includes/functions.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


$currentPage = $_SERVER["PHP_SELF"];

//used for all below
mysql_select_db($database_aquiescedb, $aquiescedb);

if(isset($_POST['setregionID'])) {
	// changing site - set sectionID back to default
	unset($_GET['sectionID']);
}

if(isset($_GET['revertID'])) {
	$select = "SELECT ID FROM article WHERE versionofID = ".intval($_GET['revertID'])." ORDER BY modifieddatetime DESC LIMIT 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($result)>0) {
		// we have a version to go back to...
		$oldarticle = mysql_fetch_assoc($result);
		$delete = "DELETE FROM article WHERE ID = ".intval($_GET['revertID']);
		mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
		$update = "UPDATE article SET ID = ".intval($_GET['revertID']).", versionofID = NULL WHERE ID = ".$oldarticle['ID'];
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	}
	
}




if(isset($_GET['deleteID'])) {
	$deleterank = defined("ARTICLE_DELETE_RANK") ? ARTICLE_DELETE_RANK : 9;
	 if($_SESSION['MM_UserGroup']>=$deleterank) {
		 // delete article functionality  if web admin
	$delete = "DELETE FROM article WHERE ID = ".GetSQLValueString($_GET['deleteID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error()); 
	$delete = "DELETE FROM article WHERE versionofID = ".GetSQLValueString($_GET['deleteID'], "int");
	$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	 } else {
		 $update = "UPDATE article SET statusID = 2 WHERE ID = ".GetSQLValueString($_GET['deleteID'], "int");
		 $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	 }
}

if(isset($_GET['duplicateID'])) {
	$newID = duplicateMySQLRecord ("article", $_GET['duplicateID']) ;
	if($newID>0) {
		$update = "UPDATE article SET statusID = 0, title = CONCAT(title, ' (copy)'), longID = NULL, versionofID = NULL, editedbyID = NULL, editeddatetime = NULL, createdbyID=".$row_rsLoggedIn['ID'].", createddatetime = NOW(), modifiedbyID = ".$row_rsLoggedIn['ID'].", modifieddatetime = NOW(), ordernum = ".$newID." WHERE ID = ".$newID;
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update); 
		header("location: update_article.php?articleID=".$newID); exit;
	}
	

}




if (isset($_GET['resetorder'])) {  // reset order
mysql_select_db($database_aquiescedb, $aquiescedb);
$query = "UPDATE article SET ordernum = ID";
$result = mysql_query($query, $aquiescedb) or die(mysql_error());
$query = "UPDATE article SET sectionID = 1 WHERE sectionID IS NULL OR sectionID < 1";
$result = mysql_query($query, $aquiescedb) or die(mysql_error());
$query = "UPDATE articlesection SET ordernum = ID";
$result = mysql_query($query, $aquiescedb) or die(mysql_error());
header("location: index.php"); exit;
}



$maxRows_rsArticles = 200;
$pageNum_rsArticles = 0;
if (isset($_GET['pageNum_rsArticles'])) {
  $pageNum_rsArticles = $_GET['pageNum_rsArticles'];
}
$startRow_rsArticles = $pageNum_rsArticles * $maxRows_rsArticles;

$varSectionID_rsArticles = "-1000";
if (isset($_GET['sectionID'])) {
  $varSectionID_rsArticles = $_GET['sectionID'];
}
$varShowAll_rsArticles = "0";
if (isset($_GET['showall'])) {
  $varShowAll_rsArticles = $_GET['showall'];
}
$varShowVersions_rsArticles = "0";
if (isset($_GET['showversions'])) {
  $varShowVersions_rsArticles = $_GET['showversions'];
}
$varRegionID_rsArticles = "0";
if (isset($regionID)) {
  $varRegionID_rsArticles = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = sprintf("SELECT article.ID, article.longID, article.title, articlesection.`description`, article.statusID, articlesection.regionID AS sectionregionID, articlesection.longID AS sectionlongID, region.title AS region, homeregion.title AS homeregion,  article.sectionID, articlesection.ordernum AS secOrder, article.ordernum, parent.ID AS parentsectionID, parent.description AS parentname, article.accesslevel, articlesection.accesslevel AS sectionaccesslevel, article.redirectURL , sectionread.name AS sectionreadname,   articleread.name AS articlereadname FROM article LEFT JOIN  articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN region ON (articlesection.regionID = region.ID) LEFT JOIN region AS homeregion ON (article.regionID = homeregion.ID) LEFT JOIN articlesection AS parent ON (articlesection.subsectionofID = parent.ID) LEFT JOIN usertype AS sectionread ON (articlesection.accesslevel = sectionread.ID)  LEFT JOIN usertype AS articleread ON (article.accesslevel = articleread.ID) WHERE (article.sectionID = %s OR %s = -1000) AND (%s < 1  OR articlesection.regionID = %s  OR articlesection.regionID = 0 OR (article.sectionID < 1 AND article.regionID = %s))  AND (%s = 1 OR versionofID IS NULL) AND (%s= 1 OR article.statusID <2) ORDER BY articlesection.ordernum, articlesection.ID, article.ordernum, article.createddatetime", GetSQLValueString($varSectionID_rsArticles, "int"),GetSQLValueString($varSectionID_rsArticles, "int"),GetSQLValueString($varRegionID_rsArticles, "int"),GetSQLValueString($varRegionID_rsArticles, "int"),GetSQLValueString($varRegionID_rsArticles, "int"),GetSQLValueString($varShowVersions_rsArticles, "int"),GetSQLValueString($varShowAll_rsArticles, "int"));
$query_limit_rsArticles = sprintf("%s LIMIT %d, %d", $query_rsArticles, $startRow_rsArticles, $maxRows_rsArticles);
$rsArticles = mysql_query($query_limit_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);

if (isset($_GET['totalRows_rsArticles'])) {
  $totalRows_rsArticles = $_GET['totalRows_rsArticles'];
} else {
  $all_rsArticles = mysql_query($query_rsArticles);
  $totalRows_rsArticles = mysql_num_rows($all_rsArticles);
}
$totalPages_rsArticles = ceil($totalRows_rsArticles/$maxRows_rsArticles)-1;

if(!isset($_GET['sectionID']) && $totalRows_rsArticles==0) { // if no pages, create home paage at least
if(createArticle(1,  $regionID, "Home page",  "<h1>Home Page</h1><p>Enter your content</p>", "", "", 1, 1, "", 0, "", $row_rsLoggedIn['ID'])) {
header("location: index.php"); exit;
}

}// end create home page

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT ID, useregions, usesections FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsSection = "-1";
if (isset($_GET['sectionID'])) {
  $colname_rsSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSection = sprintf("SELECT * FROM articlesection WHERE ID = %s", GetSQLValueString($colname_rsSection, "int"));
$rsSection = mysql_query($query_rsSection, $aquiescedb) or die(mysql_error());
$row_rsSection = mysql_fetch_assoc($rsSection);
$totalRows_rsSection = mysql_num_rows($rsSection);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs";
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);

$varRegionID_rsSections = "0";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT articlesection.ID, articlesection.`description`, parentsection.`description`AS parent FROM articlesection LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE  %s = 0 OR articlesection.regionID = %s OR articlesection.regionID= 0 ORDER BY articlesection.ordernum", GetSQLValueString($varRegionID_rsSections, "int"),GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$queryString_rsArticles = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsArticles") == false && 
        stristr($param, "totalRows_rsArticles") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsArticles = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsArticles = sprintf("&totalRows_rsArticles=%d%s", $totalRows_rsArticles, $queryString_rsArticles);
?>
<?php // test if Article prefs has been creted - if not, create it
if($totalRows_rsArticlePrefs<1) {
	$insert= "INSERT INTO `articleprefs` (`ID`) VALUES (1);"; // the rest will be defaults
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
}// end no rows

if(defined("SAVE_MENU") && $regionID ==1) { // save menu as include rather than querying database
		$html = buildMenu(0,4);
		saveFile(UPLOAD_ROOT."menu/fullMenu.inc.php",$html);
	}
	 $_GET['sectionID'] = isset($_GET['sectionID']) ? $_GET['sectionID'] : -1000; ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Pages"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/defaultArticles.css" rel="stylesheet" >
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	<?php if(isset($_GET['sectionID']) && $_GET['sectionID']>0 && $row_rsArticles['sectionID'] !=0) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=article&"+order); 
            } 
        }); 
		<?php } ?>
    }); 
</script>
<style><!--
td.section0:before {
	color:black;
	font-family:'Glyphicons Halflings';
	content:"\e021";
}

td.section-1:before {
	color:black;
	font-family:'Glyphicons Halflings';
	content:"\e022";
}

td.section {
	font-style:italic;
}
<?php if(!isset($draganddrop)) { 
echo ".handle { display:none !important; }\n";
} ?>
<?php 
if (strcmp($row_rsPreferences['usesections'],1)) { echo "
.section {display:none !important; }\n";
} 
if (strcmp($row_rsPreferences['useregions'],1)) { echo "
.region {display:none !important; }\n"; 
}?>
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
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page articles"> <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
<h1><i class="glyphicon glyphicon-file"></i> Page Manager<?php echo isset($row_rsSection['description']) ? ": ".$row_rsSection['description'] : ""; ?></h1>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  <li class="nav-item"><a class="nav-link" href="add_article.php<?php echo isset($_GET['sectionID']) ? "?sectionID=".intval($_GET['sectionID']) : "" ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add a page</a></li><li class="section"><a  class="nav-link" href="sections/index.php" ><i class="glyphicon glyphicon-list"></i> Manage Sections</a></li>
  <li  class="nav-item merge"><a  class="nav-link" href="merge/index.php"  data-toggle="tooltip" title="If a single piece of content appears across several pages you can insert it as an Add-in and edit in one place"><i class="glyphicon glyphicon-edit"></i> Add-ins</a></li>
  <li  class="nav-item"><a  class="nav-link" href="../../core/admin/preferences.php" ><i class="glyphicon glyphicon-th-list"></i> Header &amp; Footer</a></li>
  <li class="nav-item"><a  class="nav-link" href="find_and_replace.php" data-toggle="tooltip" title="You can easily batch update all pages by replacing a word or phrase across  the site"><i class="glyphicon glyphicon-search"></i></i> Find &amp; Replace</a></li>
  
   <li class="nav-item"><a  class="nav-link" href="autocreate/index.php"  data-toggle="tooltip" title="You can quickly add standard pages such as Privacy Policy here"><i class="glyphicon glyphicon-duplicate"></i> Auto Create</a></li>
   
   
   
  <li class="nav-item"><a  class="nav-link"  href="options/index.php" ><i class="glyphicon glyphicon-cog"></i> Options</a></li>
</ul></div></nav>
<?php if ($totalRows_rsSections > 0 || $totalRows_rsRegions > 1) { // Show if recordset not empty ?>
  <form action="index.php" method="get" name="formshow" id="formshow" class="form-group form-inline" >
    Show:
      
    <select name="sectionID" id="sectionID" onChange="this.form.submit()" class="section form-control">
      <option value="-1000" <?php if (!(strcmp(-1000, @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?> >All sections</option><option value="-1" <?php if (!(strcmp(-1, @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?>>Templates</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], @$_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsSections['parent']) ? $row_rsSections['parent']." &rsaquo; " : ""; echo  $row_rsSections['description'];  ?></option>
      <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
  </select> <span id="info"><span class="glyphicon glyphicon-arrow-left"></span> choose a section to drag and drop re-order</span> &nbsp;&nbsp;<label>
    <input name="showall" type="checkbox" id="showall" onClick="this.form.submit();" value="1" <?php if(isset($_GET['showall'])) echo "checked"; ?>>
    Show inactive</label> <?php if($_SESSION['MM_UserGroup']==10) { ?>
  &nbsp;&nbsp;<label>
    <input name="showversions" type="checkbox" id="showversions" onClick="this.form.submit();" value="1" <?php if(isset($_GET['showversions'])) echo "checked"; ?>>
    Show versions (wadmin)</label><?php } ?>
  </form>
  <?php } // Show if recordset not empty ?>
<?php if ($totalRows_rsArticles == 0) { // Show if recordset empty ?>
<p>There are currently no pages. </p>
<?php } // Show if recordset empty ?><?php if ($totalRows_rsArticles > 0) { // Show if recordset not empty ?>
<p class="text-muted">Pages <?php echo ($startRow_rsArticles + 1) ?> to <?php echo min($startRow_rsArticles + $maxRows_rsArticles, $totalRows_rsArticles) ?> of <?php echo $totalRows_rsArticles ?> <span id="info"></span><?php echo createPagination($pageNum_rsArticles,$totalPages_rsArticles,"rsArticles");?></p>
    <div class="table-responsive">
<table class="table table-hover">
 <thead><tr>
  <th class="handle" ></th> <th></th>
 
    <th></th><th></th>
    <th>Menu Name</th>
    <th class="section">Parent &rsaquo; Section</th>
    <th class="region">Site</th>
    <th data-toggle="tooltip" class="text-right" title="Shows number of recent page hits. Click on number for more detailed stats">Visits</th>
    <th>Actions</th>
     <th  class="debug hidden-xs">Section ID</th><th  class="debug hidden-xs">Admin ID</th><th  class="debug hidden-xs">Admin Ord</th><th  class="debug hidden-xs">Undo</th>
   </tr></thead><tbody class="sortable">
  <?php do {  if($row_rsArticles['accesslevel']>$row_rsArticles['sectionaccesslevel']) {
	   $accesslevel = $row_rsArticles['accesslevel'];
	   $accessname = $row_rsArticles['articlereadname']>0 ? $row_rsArticles['articlereadname'] : "everyone" ;
  } else {
	  $accesslevel = $row_rsArticles['sectionaccesslevel'];
	  $accessname = $row_rsArticles['sectionaccesslevel']>0 ? $row_rsArticles['sectionreadname'] : "everyone";
  }
  
  
  ?>
  <tr  id="listItem_<?php echo $row_rsArticles['ID']; ?>" ><td class= "handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of pages">&nbsp;</td>
    <td class="status<?php echo $row_rsArticles['statusID']; ?> section<?php echo $row_rsArticles['sectionID']; ?>" data-toggle="tooltip" title="
  <?php switch ($row_rsArticles['sectionID']) { case 0 : echo "This is the home page. "; break; case -1 : "This is a template. "; break; } switch($row_rsArticles['statusID']) { case 0 : echo "This page is pending approval and is not viewable"; break; case 1 : echo "This page is live"; break; default: echo "This page is inactive and not viewable"; } ?>">&nbsp;</td>
    
    
    
    <td><?php if(strlen($row_rsArticles['redirectURL'])>0) { ?><img src="/core/images/icons/edit-redo.png" alt="Redirect" width="16" height="16" style="vertical-align:
middle;" data-toggle="tooltip" title="This page redirects to <?php echo $row_rsArticles['redirectURL']; ?>" >
    <?php } ?></td><td class="access level<?php echo $accesslevel; ?>" data-toggle="tooltip" title="This page is viewable by <?php echo $accessname; ?>">&nbsp;</td><td><a href="update_article.php?articleID=<?php echo $row_rsArticles['ID']; ?>&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" data-toggle="tooltip" title = "Click to edit this page"><?php echo $row_rsArticles['title']; ?></a>&nbsp;</td>
    <td class="section" <?php if(isset($row_rsArticles['description'])) { ?>data-toggle="tooltip" title="This is the section that this page appears in. Click to edit this section."<?php } ?>><?php echo isset($row_rsArticles['parentname']) ? "<a href=\"sections/update_section.php?sectionID=".$row_rsArticles['parentsectionID']."\">".$row_rsArticles['parentname']."</a> &rsaquo; " : ""; echo "<a href=\"sections/update_section.php?sectionID=".$row_rsArticles['sectionID']."\">".$row_rsArticles['description']."</a>"; echo isset($row_rsArticles['subArticleofTitle']) ? "<em> - ".$row_rsArticles['subArticleofTitle']."</em>" : "";  ?>&nbsp;</td><td class="region"><?php echo ($row_rsArticles['sectionID']==0) ? $row_rsArticles['homeregion'] : (($row_rsArticles['sectionregionID']==0) ? "ALL" : $row_rsArticles['region']); ?></td>
    
    <?php $articleLink = articleLink($row_rsArticles['ID'], $row_rsArticles['longID'], $row_rsArticles['sectionID'], $row_rsArticles['sectionlongID']) ; ?>
              <td class="text-right"><a href="/core/seo/admin/visitors/page.php?page=<?php echo urlencode($articleLink); ?>" ><span id="visitor_count_<?php echo $row_rsArticles['ID']; ?>"><script>
              ajaxManager.addReq({
           type: 'GET',
           url: '/core/seo/admin/visitors/ajax/visitors.ajax.php?page=<?php echo urlencode($articleLink); ?>',
          
           success: function(data){
              $("#visitor_count_<?php echo $row_rsArticles['ID']; ?>").html(data);
           }
       });
       </script></span></a></td>
       
        <td nowrap><div class="btn-group" role="group" ><a href="update_article.php?articleID=<?php echo $row_rsArticles['ID']; ?>&returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-default btn-secondary" data-toggle="tooltip" title="Edit this page"><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="javascript:void(0);" onClick="prompt('You can copy the URL for this page to your clipboard below:','<?php 
	$url = getProtocol()."://".$_SERVER['HTTP_HOST']; 
	
	 $url .= $articleLink ;  echo $url; ?>')" class="btn btn-sm btn-default btn-secondary" data-toggle="tooltip" title="Link to this page" ><i class="glyphicon glyphicon-link"></i> Link</a> 
     
     
     <!-- Single button -->
<div class="btn-group">
  <button type="button" class="btn btn-sm btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="glyphicon glyphicon-cog"></i> Actions <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
    <li><a href="<?php  $articleLink .=   strpos($url, "?")>0 ? "&" : "?";$articleLink .= "preview=true&regionID=".intval($regionID);  echo $articleLink; ?>" title="View this page" target="_blank" rel="noopener" data-toggle="tooltip"><i class="glyphicon glyphicon-search"></i> View</a></li>
    <li><?php if($row_rsArticles['sectionID'] !=0) { ?><a href="index.php?duplicateID=<?php echo $row_rsArticles['ID']; ?>" onClick="return confirm('Are you sure you wish to create a duplicate copy of this page?\n\nThe new copy will open in the editor.');"  data-toggle="tooltip" title="Duplicate this page" ><i class="glyphicon glyphicon-duplicate"></i> Duplicate</a><?php } ?></li>
    <li><?php if($row_rsArticles['sectionID'] !=0) { ?><a href="index.php?deleteID=<?php echo $row_rsArticles['ID']; ?>" onClick="return confirm('Are you sure you wish to delete this page?\n\nWarning: you can not undo this action.');"  data-toggle="tooltip" title="Delete this page"><i class="glyphicon glyphicon-trash"></i> Delete</a><?php } ?></li>
  </ul>
</div><!-- end button group-->


     </div><!-- end button group-->
     
     </td>
    
   
    
    
    
    <td class="debug hidden-xs"><?php echo $row_rsArticles['sectionID']; ?></td>
    <td class="debug hidden-xs"><?php echo $row_rsArticles['ID']; ?></td>
    <td class="debug hidden-xs" ><?php echo $row_rsArticles['ordernum']; ?></td>
    <td class="debug hidden-xs" ><a href="index.php?revertID=<?php echo $row_rsArticles['ID']; ?>" onClick="return confirm('Are you sure you want to revert this page to the previous saved version?')">Undo</a></td>
   </tr>
  <?php 
   } while ($row_rsArticles = mysql_fetch_assoc($rsArticles)); ?>
</tbody></table></div>
<?php echo createPagination($pageNum_rsArticles,$totalPages_rsArticles,"rsArticles");?><?php } ?><table class="form-table">
  <tr>
    <td class="top">&nbsp;</td>
    <td class="top"><strong>Key</strong></td>
  </tr>
  <tr>
    <td class="top"><img src="../../core/images/icons/green-light.png" alt="Article is active" width="16" height="16" style="vertical-align:
middle;" /></td>
    <td class="top">Page is displayed on the site</td>
  </tr>
  <tr>
    <td class="top"><img src="../../core/images/icons/amber-light.png" alt="Article is pending approval" width="16" height="16" style="vertical-align:
middle;" /></td>
    <td class="top">Page is pending approval and not displayed</td>
  </tr>
  <tr>
    <td class="top"><img src="../../core/images/icons/red-light.png" alt="Article is disabled, either because it has been set to not display, rejected or the parent section is inactive" width="16" height="16" style="vertical-align:
middle;" /></td>
    <td class="top">Page is disabled, either because it has been set to not display, rejected or the parent section is inactive</td>
  </tr>
</table>

  <?php if($row_rsLoggedIn['usertypeID']==10) { ?>
 <p> Administrator: <a href="index.php?resetorder=true">Reset article/section order</a></p>
  <?php } ?>

</div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticles);

mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsSection);

mysql_free_result($rsRegions);

mysql_free_result($rsArticlePrefs);

mysql_free_result($rsSections);
?>
