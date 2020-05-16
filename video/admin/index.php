<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET videoupload=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['videoupload']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsVideos = 100;
$pageNum_rsVideos = 0;
if (isset($_GET['pageNum_rsVideos'])) {
  $pageNum_rsVideos = $_GET['pageNum_rsVideos'];
}
$startRow_rsVideos = $pageNum_rsVideos * $maxRows_rsVideos;

$varRegionID_rsVideos = "1";
if (isset($regionID)) {
  $varRegionID_rsVideos = $regionID;
}
$varCategoryID_rsVideos = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsVideos = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideos = sprintf("SELECT video.*, videocategory.categoryname FROM video LEFT JOIN videocategory ON video.categoryID = videocategory.ID WHERE videocategory.regionID = %s AND (%s = 0 OR  videocategory.ID = %s) ORDER BY videocategory.ordernum ASC, video.ordernum ASC", GetSQLValueString($varRegionID_rsVideos, "int"),GetSQLValueString($varCategoryID_rsVideos, "int"),GetSQLValueString($varCategoryID_rsVideos, "int"));
$query_limit_rsVideos = sprintf("%s LIMIT %d, %d", $query_rsVideos, $startRow_rsVideos, $maxRows_rsVideos);
$rsVideos = mysql_query($query_limit_rsVideos, $aquiescedb) or die(mysql_error());
$row_rsVideos = mysql_fetch_assoc($rsVideos);

if (isset($_GET['totalRows_rsVideos'])) {
  $totalRows_rsVideos = $_GET['totalRows_rsVideos'];
} else {
  $all_rsVideos = mysql_query($query_rsVideos);
  $totalRows_rsVideos = mysql_num_rows($all_rsVideos);
}
$totalPages_rsVideos = ceil($totalRows_rsVideos/$maxRows_rsVideos)-1;

$varRegionID_rsVideoCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsVideoCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategories = sprintf("SELECT * FROM videocategory WHERE regionID = %s ORDER BY ordernum, categoryname ASC", GetSQLValueString($varRegionID_rsVideoCategories, "int"));
$rsVideoCategories = mysql_query($query_rsVideoCategories, $aquiescedb) or die(mysql_error());
$row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
$totalRows_rsVideoCategories = mysql_num_rows($rsVideoCategories);

$queryString_rsVideos = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsVideos") == false && 
        stristr($param, "totalRows_rsVideos") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsVideos = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsVideos = sprintf("&totalRows_rsVideos=%d%s", $totalRows_rsVideos, $queryString_rsVideos);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = " Manage Video"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	<?php if(isset($_GET['categoryID']) && $_GET['categoryID']>0) { $draganddrop = true;?>
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=video&"+order); 
            } 
        }); 
		<?php } ?>
    }); 
</script>
<style><!--
<?php if(!isset($draganddrop)) { 
echo ".handle { display:none !important; }\n";
} 
if ($totalRows_rsVideoCategories==0) {
	echo ".category { display:none !important; }\n";

}?>

--></style><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page video">
 <?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-film"></i> Manage Video</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
  
     <li><a href="add_video.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add video</a></li>
     
     <li><a href="categories/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Manage Categories</a></li>
     <li><a href="options/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
    </ul></div></nav>
   <form action="<?php echo $editFormAction; ?>" method="GET" name="form1" id="form1">
    
       <select name="categoryID" id="categoryID" class="category" onChange="this.form.submit()">
         <option value="0" <?php if (!(strcmp(0, $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>>All categories</option>
         <?php
do {  
?>
         <option value="<?php echo $row_rsVideoCategories['ID']?>"<?php if (!(strcmp($row_rsVideoCategories['ID'], $_GET['categoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsVideoCategories['categoryname']?></option>
         <?php
} while ($row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories));
  $rows = mysql_num_rows($rsVideoCategories);
  if($rows > 0) {
      mysql_data_seek($rsVideoCategories, 0);
	  $row_rsVideoCategories = mysql_fetch_assoc($rsVideoCategories);
  }
?>
       </select>
       
   </form>
  
<?php if ($totalRows_rsVideos == 0) { // Show if recordset empty ?>
     <p>There are no videos at present</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsVideos > 0) { // Show if recordset not empty ?>
     <p>Videos <?php echo ($startRow_rsVideos + 1) ?> to <?php echo min($startRow_rsVideos + $maxRows_rsVideos, $totalRows_rsVideos) ?> of <?php echo $totalRows_rsVideos ?> <span id="info">Choose a specific section to drag and drop re-order</span></p>
     <ul class="listTable sortable">
       <li class="tr header">
         <span class="th handle">&nbsp;</span>
          <span class="th">&nbsp;</span>
        <span class="th">Added</span> <span class="th">&nbsp;</span>
        <span class="th">Title</span>
       <span class="th">Category</span>
        <span class="th">Edit</span>
         <span class="th">Embed</span>
        </li>
       <?php do { ?>
         <li id="listItem_<?php echo $row_rsVideos['ID']; ?>"><span class="handle">&nbsp;</span>
           <span class="status<?php echo $row_rsVideos['statusID']; ?>">&nbsp;</span>
            
           <span><?php echo date('d M y',strtotime($row_rsVideos['createddatetime'])); ?></span>
            <span><?php echo isset($row_rsVideos['videoURL']) ? "<img src=\"/core/images/icons/video-x-generic.png\" width=\"16\" height=\"16\" style=\"vertical-align:middle;\"/>" : ""; ?></span>
           <span><?php echo $row_rsVideos['videotitle']; ?></span>
           <span><em><?php echo $row_rsVideos['categoryname']; ?></em></span>
           <span><a href="update_video.php?videoID=<?php echo $row_rsVideos['ID']; ?>" class="link_edit icon_only">Edit</a></span>
           <span><a href="javascript:void(0);" class="link_link" onClick="prompt('Paste the code below in to your page HTML to embed this video', '<?php $videoURL = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://"; $videoURL .= $_SERVER['HTTP_HOST']."/Uploads/".$row_rsVideos['videoURL']; echo ($row_rsVideos['method']==2) ? htmlentities('<video controls><source src="'.$videoURL.'" type="video/mp4">Your browser does not support the video tag.</video>', ENT_COMPAT, "UTF-8") : htmlentities($row_rsVideos['videoURL'], ENT_COMPAT, "UTF-8") ?>');">Link/Embed</a></span>
           </li>
         <?php } while ($row_rsVideos = mysql_fetch_assoc($rsVideos)); ?>
     </ul>
     <?php } // Show if recordset not empty ?>
<table border="0" class="form-table">
     <tr>
       <td><?php if ($pageNum_rsVideos > 0) { // Show if not first page ?>
             <a href="<?php printf("%s?pageNum_rsVideos=%d%s", $currentPage, 0, $queryString_rsVideos); ?>">First</a>
             <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsVideos > 0) { // Show if not first page ?>
             <a href="<?php printf("%s?pageNum_rsVideos=%d%s", $currentPage, max(0, $pageNum_rsVideos - 1), $queryString_rsVideos); ?>" rel="prev">Previous</a>
             <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsVideos < $totalPages_rsVideos) { // Show if not last page ?>
             <a href="<?php printf("%s?pageNum_rsVideos=%d%s", $currentPage, min($totalPages_rsVideos, $pageNum_rsVideos + 1), $queryString_rsVideos); ?>" rel="next">Next</a>
             <?php } // Show if not last page ?>       </td>
       <td><?php if ($pageNum_rsVideos < $totalPages_rsVideos) { // Show if not last page ?>
             <a href="<?php printf("%s?pageNum_rsVideos=%d%s", $currentPage, $totalPages_rsVideos, $queryString_rsVideos); ?>">Last</a>
             <?php } // Show if not last page ?>       </td>
     </tr>
   </table>
</div>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsVideos);

mysql_free_result($rsVideoCategories);
?>


