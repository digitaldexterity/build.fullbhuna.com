<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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
  $updateSQL = sprintf("UPDATE preferences SET autolinks=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['autolinks']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_GET['autolinkID'])) && ($_GET['autolinkID'] != "")) {
  $deleteSQL = sprintf("DELETE FROM keywordlinks WHERE ID=%s",
                       GetSQLValueString($_GET['autolinkID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
}

$maxRows_rsKeywordLinks = 50;
$pageNum_rsKeywordLinks = 0;
if (isset($_GET['pageNum_rsKeywordLinks'])) {
  $pageNum_rsKeywordLinks = $_GET['pageNum_rsKeywordLinks'];
}
$startRow_rsKeywordLinks = $pageNum_rsKeywordLinks * $maxRows_rsKeywordLinks;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsKeywordLinks = "SELECT keywordlinks.ID, keywordlinks.linkkeywords, keywordlinks.linkURL, keywordlinks.linktitle, keywordlinks.statusID FROM keywordlinks ORDER BY keywordlinks.createddatetime DESC";
$query_limit_rsKeywordLinks = sprintf("%s LIMIT %d, %d", $query_rsKeywordLinks, $startRow_rsKeywordLinks, $maxRows_rsKeywordLinks);
$rsKeywordLinks = mysql_query($query_limit_rsKeywordLinks, $aquiescedb) or die(mysql_error());
$row_rsKeywordLinks = mysql_fetch_assoc($rsKeywordLinks);

if (isset($_GET['totalRows_rsKeywordLinks'])) {
  $totalRows_rsKeywordLinks = $_GET['totalRows_rsKeywordLinks'];
} else {
  $all_rsKeywordLinks = mysql_query($query_rsKeywordLinks);
  $totalRows_rsKeywordLinks = mysql_num_rows($all_rsKeywordLinks);
}
$totalPages_rsKeywordLinks = ceil($totalRows_rsKeywordLinks/$maxRows_rsKeywordLinks)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT autolinks FROM preferences WHERE ID = 1 LIMIT 1";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$queryString_rsKeywordLinks = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsKeywordLinks") == false && 
        stristr($param, "totalRows_rsKeywordLinks") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsKeywordLinks = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsKeywordLinks = sprintf("&totalRows_rsKeywordLinks=%d%s", $totalRows_rsKeywordLinks, $queryString_rsKeywordLinks);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Manage Keyword Links"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  <div class="page seo"> <h1><i class="glyphicon glyphicon-globe"></i> Auto Links</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="add_link.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add Link</a></li>
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage SEO</a></li>
   </ul></div></nav>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1"><label><input <?php if (!(strcmp($row_rsPreferences['autolinks'],1))) {echo "checked=\"checked\"";} ?> name="autolinks" id="autolinks" type="checkbox" value="1"  onchange="this.form.submit()" />
   AutoLinks on by default (you can overide this setting on individual items)</label>
     <input name="ID" type="hidden" id="ID" value="1" />
     <input type="hidden" name="MM_update" value="form1" />
   </form>
   <?php if ($totalRows_rsKeywordLinks == 0) { // Show if recordset empty ?>
     <p>Autolinks will automatically add links to key words in your articles to  relevant pages. It will also automatically recognise types and links and change them into active ones.</p>
     <p>There are currently no Auto Links. Click on Add Link above to start adding links.</p>
     <?php } // Show if recordset empty ?>
 <?php if ($totalRows_rsKeywordLinks > 0) { // Show if recordset not empty ?>
       <p>Links <?php echo ($startRow_rsKeywordLinks + 1) ?> to <?php echo min($startRow_rsKeywordLinks + $maxRows_rsKeywordLinks, $totalRows_rsKeywordLinks) ?> of <?php echo $totalRows_rsKeywordLinks ?></p>
       <table  class="table table-hover"><thead>
         <tr>
           <th>&nbsp;</th>
           <th>Key word/phrase link</th>
           <th>Edit</th>
           <th>Delete</th>
         </tr></thead><tbody>
         <?php do { ?>
           <tr>
             <td><?php if ($row_rsKeywordLinks['statusID'] == 1){ ?>
                 <img src="../../../images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" />
                 <?php } else { ?>
                 <img src="../../../images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" />
                 <?php } ?></td>
             <td><a href="<?php echo $row_rsKeywordLinks['../../../../blogs/admin/keywordlinks/linkURL']; ?>" title="<?php echo $row_rsKeywordLinks['linktitle']; ?>" target="_blank" rel="noopener"><?php echo $row_rsKeywordLinks['linkkeywords']; ?></a></td>
             <td><a href="update_link.php?keywordlinkID=<?php echo $row_rsKeywordLinks['ID']; ?>" class="link_edit icon_only">Edit</a></td>
             <td><a href="index.php?autolinkID=<?php echo $row_rsKeywordLinks['ID']; ?>" onClick="return confirm('Are you sure you want to delete this AutoLink?\n\nThis link will no longer be added to future documents. Current documents will be unaffected and will require the link to be removed manually.');" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
           </tr>
           <?php } while ($row_rsKeywordLinks = mysql_fetch_assoc($rsKeywordLinks)); ?></tbody>
      </table>
      <?php } // Show if recordset not empty ?>
     <table class="form-table">
       <tr>
         <td><?php if ($pageNum_rsKeywordLinks > 0) { // Show if not first page ?>
               <a href="<?php printf("%s?pageNum_rsKeywordLinks=%d%s", $currentPage, 0, $queryString_rsKeywordLinks); ?>">First</a>
               <?php } // Show if not first page ?>         </td>
         <td><?php if ($pageNum_rsKeywordLinks > 0) { // Show if not first page ?>
               <a href="<?php printf("%s?pageNum_rsKeywordLinks=%d%s", $currentPage, max(0, $pageNum_rsKeywordLinks - 1), $queryString_rsKeywordLinks); ?>" rel="prev">Previous</a>
               <?php } // Show if not first page ?>         </td>
         <td><?php if ($pageNum_rsKeywordLinks < $totalPages_rsKeywordLinks) { // Show if not last page ?>
               <a href="<?php printf("%s?pageNum_rsKeywordLinks=%d%s", $currentPage, min($totalPages_rsKeywordLinks, $pageNum_rsKeywordLinks + 1), $queryString_rsKeywordLinks); ?>" rel="next">Next</a>
               <?php } // Show if not last page ?>         </td>
         <td><?php if ($pageNum_rsKeywordLinks < $totalPages_rsKeywordLinks) { // Show if not last page ?>
               <a href="<?php printf("%s?pageNum_rsKeywordLinks=%d%s", $currentPage, $totalPages_rsKeywordLinks, $queryString_rsKeywordLinks); ?>">Last</a>
               <?php } // Show if not last page ?>         </td>
       </tr>
     </table></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsKeywordLinks);

mysql_free_result($rsPreferences);
?>


