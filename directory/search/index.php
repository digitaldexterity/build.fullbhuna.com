<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.firstname, users.surname FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsCategories = "0";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
$varSearch_rsCategories = "%";
if (isset($_GET['s'])) {
  $varSearch_rsCategories = $_GET['s'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT directorycategory.ID, directorycategory.`description` FROM directorycategory WHERE directorycategory.regionID = %s AND directorycategory.`description` LIKE %s", GetSQLValueString($varRegionID_rsCategories, "int"),GetSQLValueString("%" . $varSearch_rsCategories . "%", "text"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$maxRows_rsDirectory = 20;
$pageNum_rsDirectory = 0;
if (isset($_GET['pageNum_rsDirectory'])) {
  $pageNum_rsDirectory = $_GET['pageNum_rsDirectory'];
}
$startRow_rsDirectory = $pageNum_rsDirectory * $maxRows_rsDirectory;

$varRegionID_rsDirectory = "0";
if (isset($regionID)) {
  $varRegionID_rsDirectory = $regionID;
}
$varSearch_rsDirectory = "%";
if (isset($_GET['s'])) {
  $varSearch_rsDirectory = $_GET['s'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT directory.ID, directory.name, directory.`description` FROM directory LEFT JOIN directorycategory ON (directory.categoryID = directorycategory.ID) WHERE directorycategory.regionID = %s AND (directory.`description` LIKE %s OR directory.name LIKE %s)", GetSQLValueString($varRegionID_rsDirectory, "int"),GetSQLValueString("%" . $varSearch_rsDirectory . "%", "text"),GetSQLValueString("%" . $varSearch_rsDirectory . "%", "text"));
$query_limit_rsDirectory = sprintf("%s LIMIT %d, %d", $query_rsDirectory, $startRow_rsDirectory, $maxRows_rsDirectory);
$rsDirectory = mysql_query($query_limit_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);

if (isset($_GET['totalRows_rsDirectory'])) {
  $totalRows_rsDirectory = $_GET['totalRows_rsDirectory'];
} else {
  $all_rsDirectory = mysql_query($query_rsDirectory);
  $totalRows_rsDirectory = mysql_num_rows($all_rsDirectory);
}
$totalPages_rsDirectory = ceil($totalRows_rsDirectory/$maxRows_rsDirectory)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryPrefs = "SELECT * FROM directoryprefs";
$rsDirectoryPrefs = mysql_query($query_rsDirectoryPrefs, $aquiescedb) or die(mysql_error());
$row_rsDirectoryPrefs = mysql_fetch_assoc($rsDirectoryPrefs);
$totalRows_rsDirectoryPrefs = mysql_num_rows($rsDirectoryPrefs);

$queryString_rsDirectory = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsDirectory") == false && 
        stristr($param, "totalRows_rsDirectory") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsDirectory = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsDirectory = sprintf("&totalRows_rsDirectory=%d%s", $totalRows_rsDirectory, $queryString_rsDirectory);
?>
<?php $accesslevel = $row_rsDirectoryPrefs['accesslevel']; require_once('../../members/includes/restrictaccess.inc.php'); ?>

<?php if ($totalRows_rsDirectory == 1 && $totalRows_rsCategories ==0) { // exact company  match, so go straight to page
header("Location: /directory/directory.php?directoryID=".$row_rsDirectory['ID']);exit;
} ?>
<?php if ($totalRows_rsDirectory == 0 && $totalRows_rsCategories ==1) { // exact category  match, so go straight to page
header("Location: /directory/index.php?categoryID=".$row_rsCategories['ID']);exit;
} ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Search Results"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
      <h1 class="directoryheader">Search Results</h1>
      
    <?php if ($totalRows_rsCategories > 0) { // Show if recordset not empty ?>
        <h2>Matching Directory Categories:</h2>
        <p>
          <?php do { ?>
            <a href="../index.php?categoryID=<?php echo $row_rsCategories['ID']; ?>"><?php echo str_ireplace($_GET['s'],"<span class = \"highlight\">".htmlentities($_GET['s'], ENT_COMPAT, "UTF-8")."</span>",$row_rsCategories['description']); ?></a>;&nbsp;&nbsp;
            <?php } while ($row_rsCategories = mysql_fetch_assoc($rsCategories)); ?>
        </p>
        <?php } // Show if recordset not empty ?>
      <?php if ($totalRows_rsDirectory > 0) { // Show if recordset not empty ?>
  <h2>Matching Directory Entries:</h2>
  <p class="text-muted">Entries <?php echo ($startRow_rsDirectory + 1) ?> to <?php echo min($startRow_rsDirectory + $maxRows_rsDirectory, $totalRows_rsDirectory) ?> of <?php echo $totalRows_rsDirectory ?> </p>
  <?php do { ?>
    
    <p><strong><a href="../directory.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>"><?php echo  str_ireplace($_GET['s'],"<span class = \"highlight\">".htmlentities($_GET['s'], ENT_COMPAT, "UTF-8")."</span>",$row_rsDirectory['name']); ?></a></strong><br />
      <?php echo  str_replace($_GET['s'],"<span class = \"highlight\">".htmlentities($_GET['s'], ENT_COMPAT, "UTF-8")."</span>",$row_rsDirectory['description']); ?></p>
    <?php } while ($row_rsDirectory = mysql_fetch_assoc($rsDirectory)); ?>
  <?php } // Show if recordset not empty ?>
  <table class="form-table">
    <tr>
      <td><?php if ($pageNum_rsDirectory > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, 0, $queryString_rsDirectory); ?>">First</a>
          <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, max(0, $pageNum_rsDirectory - 1), $queryString_rsDirectory); ?>" rel="prev">Previous</a>
          <?php } // Show if not first page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory < $totalPages_rsDirectory) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, min($totalPages_rsDirectory, $pageNum_rsDirectory + 1), $queryString_rsDirectory); ?>" rel="next">Next</a>
          <?php } // Show if not last page ?>
      </td>
      <td><?php if ($pageNum_rsDirectory < $totalPages_rsDirectory) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_rsDirectory=%d%s", $currentPage, $totalPages_rsDirectory, $queryString_rsDirectory); ?>">Last</a>
          <?php } // Show if not last page ?>
      </td>
    </tr>
  </table>
  <?php if ($totalRows_rsDirectory == 0 && $totalRows_rsCategories ==0) { ?><p>Sorry your query didn't match anything in the directory.</p>
  <p>
    <?php } ?>
  </p>
  <form action="index.php" method="get" enctype="multipart/form-data" name="form1" id="form1" class="form-inline">
   
    <input name="s" type="text"  id="s" value="<?php echo htmlentities($_GET['s'], ENT_COMPAT, "UTF-8"); ?>" size="20" maxlength="50"  class="form-control"/>
    <label for="searchagain"></label>
    <button name="searchagain" type="submit" class="btn btn-default btn-secondary" id="searchagain" >Search again</button>
  </form>
  <p>&nbsp; </p>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsCategories);

mysql_free_result($rsDirectory);

mysql_free_result($rsDirectoryPrefs);
?>
